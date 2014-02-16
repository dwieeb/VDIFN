<?php

namespace PlantPath\Bundle\VDIFNBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;

class VDIFNDailyBackupCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        // Default date is today.
        $date = new \DateTime();

        $this
            ->setName('vdifn:daily-backup')
            ->setDescription('Create an archive backup of a day of NOAA data')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Specify a date for which to archive NOAA data (Format: Ymd)', $date->format('Ymd'))
            ->addOption('remove', 'r', InputOption::VALUE_NONE, 'Remove the NOAA data after archiving');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');
        $ymd = $input->getOption('date');
        $directory = dirname(sprintf($this->getContainer()->getParameter('vdifn.noaa_path'), $ymd, ''));

        $logger->info('Starting daily backup.', ['ymd' => $ymd]);

        $process = new Process("cd $directory; tar -czvf $directory.tar.gz *");
        $process->run();

        if ($input->getOption('remove')) {
            $logger->info('Removing directory as requested: ' . $directory);
            $fs = new Filesystem();
            $fs->remove($directory);
        }

        $logger->info('Finished daily backup.');
    }
}
