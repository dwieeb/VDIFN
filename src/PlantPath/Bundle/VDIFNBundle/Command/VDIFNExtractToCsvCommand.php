<?php

namespace PlantPath\Bundle\VDIFNBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class VDIFNExtractToCsvCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        // Default date is today.
        $date = new \DateTime();

        $this
            ->setName('vdifn:extract-to-csv')
            ->setDescription('Make a CSV file from a NOAA data file')
            ->addArgument('file', InputArgument::REQUIRED, 'The file path to the NOAA data file')
            ->addArgument('csv', InputArgument::OPTIONAL, 'The file path to the generated CSV file')
            ->addOption('remove', 'r', InputOption::VALUE_NONE, 'Remove the original file after extracting');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filepath = $input->getArgument('file');

        if (!file_exists($filepath)) {
            throw new \RuntimeException('File not found at: ' . $filepath);
        }

        $csvpath = null === $input->getArgument('csv') ? $filepath . '.csv' : $input->getArgument('csv');

        $builder = new ProcessBuilder();
        $builder->setPrefix($this->getContainer()->getParameter('wgrib2_path'));
        $builder->setArguments([$filepath, '-csv', $csvpath]);

        $process = $builder->getProcess();
        $process->run();

        if ($input->getOption('remove')) {
            unlink($filepath);
        }
    }
}
