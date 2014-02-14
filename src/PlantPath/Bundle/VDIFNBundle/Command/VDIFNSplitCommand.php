<?php

namespace PlantPath\Bundle\VDIFNBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Filesystem\Filesystem;

class VDIFNSplitCommand extends ContainerAwareCommand
{
    /**
     * @var Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('vdifn:split')
            ->setDescription('Split the data from NOAA into seperate file chunks')
            ->addArgument('file', InputArgument::REQUIRED, 'The file path to the NOAA data file')
            ->addOption('fields', 'f', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Specify inventory record numbers by which to split')
            ->addOption('remove', 'r', InputOption::VALUE_NONE, 'Remove the original file after splitting');
    }

    /**
     * Given an InputInterface, return a sanitized array of field IDs. Defaults
     * to configuration value if no fields are supplied.
     *
     * @return array
     */
    protected function getFields()
    {
        $fields = $this->input->getOption('fields');

        if (empty($fields)) {
            $fields = $this->getContainer()->getParameter('vdifn.noaa_fields');
        }

        return $this->filterFields($fields);
    }

    /**
     * Given an array of fields, filter out the invalid values and run valid
     * values through filter_var to turn them into integers and return the
     * result.
     *
     * @param  array  $fields
     *
     * @return array
     */
    public function filterFields(array $fields)
    {
        return array_filter(array_map(function($value) {
            if (false !== $value = filter_var($value, FILTER_VALIDATE_INT)) {
                return $value;
            }
        }, $fields));
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
        $this->input = $input;
        $filepath = $this->input->getArgument('file');

        if (!file_exists($filepath)) {
            throw new \RuntimeException('File does not exist: ' . $filepath);
        }

        if (false === $filesize = filesize($filepath)) {
            throw new \RuntimeException('Unable to determine filesize of file: ' . $filepath);
        }

        $fields = $this->getFields($this->input);
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

        if ($this->input->getOption('remove')) {
            $fs = new Filesystem();
            $fs->remove($filepath);
        }
    }
}
