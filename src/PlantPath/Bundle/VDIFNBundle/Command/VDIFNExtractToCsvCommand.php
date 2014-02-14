<?php

namespace PlantPath\Bundle\VDIFNBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Filesystem\Filesystem;

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

        // TODO: Add options for undefine. They should not do this by default.
        $n = $this->getContainer()->getParameter('vdifn.bounding_box.coordinates.n');
        $e = $this->getContainer()->getParameter('vdifn.bounding_box.coordinates.e');
        $s = $this->getContainer()->getParameter('vdifn.bounding_box.coordinates.s');
        $w = $this->getContainer()->getParameter('vdifn.bounding_box.coordinates.w');

        $builder = new ProcessBuilder();
        $builder->setPrefix($this->getContainer()->getParameter('wgrib2_path'));
        $builder->setArguments([$filepath, '-undefine', 'out-box', $w . ':' . $e, $s . ':' . $n, '-csv', $csvpath]);

        $process = $builder->getProcess();
        $process->run();

        if ($input->getOption('remove')) {
            $fs = new Filesystem();
            $fs->remove($filepath);
        }
    }
}
