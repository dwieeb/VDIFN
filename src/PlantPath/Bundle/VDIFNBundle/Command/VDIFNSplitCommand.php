<?php

namespace PlantPath\Bundle\VDIFNBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class VDIFNSplitCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        // Default date is today.
        $date = new \DateTime();

        $this
            ->setName('vdifn:split')
            ->setDescription('Split the data from NOAA into seperate file chunks')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Specify a date for which NOAA data file to split (Format: Ymd)', $date->format('Ymd'))
            ->addOption('fields', 'f', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Specify inventory record numbers by which to split')
            ->addOption('remove', 'r', InputOption::VALUE_NONE, 'Remove the original file after splitting');
    }

    /**
     * Given an InputInterface, return a sanitized array of field IDs. Defaults
     * to configuration value if no fields are supplied.
     *
     * @param  InputInterface $input
     *
     * @return array
     */
    protected function getFields(InputInterface $input)
    {
        $fields = $input->getOption('fields');

        if (empty($fields)) {
            $fields = $this->getContainer()->getParameter('vdifn.noaa_fields');
        } else {
            // Change all number strings to integers and remove any that didn't validate as an integer.
            $fields = array_filter(array_map(function($value) {
                if (false !== $value = filter_var($value, FILTER_VALIDATE_INT)) {
                    return $value;
                }
            }, $fields));
        }

        return $fields;
    }

    /**
     * Given a filepath to a NOAA data file, use wgrib2 to list out the
     * inventory of that file.
     *
     * @param  string $filepath
     *
     * @return array
     */
    protected function getInventory($filepath)
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix($this->getContainer()->getParameter('wgrib2_path'));
        $builder->setArguments([$filepath]);

        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $inventory = explode(PHP_EOL, trim($process->getOutput()));

        foreach ($inventory as &$item) {
            $item = explode(':', $item);
        }

        return $inventory;
    }

    /**
     * Returns a process that splits up a NOAA data file given a filepath,
     * byte number at which to skip and byte number for length.
     *
     * @param string $input The input file path.
     * @param string $skip The byte number to skip.
     * @param string $count The byte number for length.
     * @param string $output The output file path.
     *
     * @return Symfony\Component\Process\Process
     */
    protected function createSplitProcess($input, $skip, $count, $output)
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix('dd');
        $builder->setArguments(["if=$input", "ibs=1", "skip=$skip", "count=$count", "of=$output"]);

        return $builder->getProcess();
    }

    /**
     * Given an array of Process objects, determine if at least one is still
     * running. If so, return true, otherwise return false.
     *
     * @param  array  $processes
     *
     * @return boolean
     */
    protected function processesAreRunning(array $processes)
    {
        foreach ($processes as $process) {
            if ($process->isRunning()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ymd = $input->getOption('date');
        $filepath = sprintf($this->getContainer()->getParameter('vdifn.noaa_path'), $ymd);

        if (!file_exists($filepath)) {
            $command = $this->getApplication()->find('vdifn:download');

            // An exit status code of 0 means the command terminated successfully.
            if (0 !== $command->run(new ArrayInput([ 'command' => 'vdifn:download', '--date' => $ymd ]), $output)) {
                throw new \RuntimeException('vdifn:download command failed');
            }
        }

        if (false === $filesize = filesize($filepath)) {
            throw new \RuntimeException('Unable to determine filesize of file: ' . $filepath);
        }

        $fields = $this->getFields($input);
        $inventory = $this->getInventory($filepath);
        $processes = [];

        for ($i = 0; $i < count($inventory); ++$i) {
            $fieldNumber = $inventory[$i][0];

            if (in_array($fieldNumber, $fields)) {
                $byteStart = $inventory[$i][1];
                $byteEnd = array_key_exists($i + 1, $inventory) ? $inventory[$i + 1][1] : $filesize;
                $process = $this->createSplitProcess($filepath, $byteStart, $byteEnd - $byteStart, $filepath . '.' . $inventory[$i][0]);
                $processes[] = $process;
                $process->start();
            }
        }

        while ($this->processesAreRunning($processes)) {
            // Waiting for asynchronous processes to finish.
        }

        if ($input->getOption('remove')) {
            unlink($filepath);
        }
    }
}
