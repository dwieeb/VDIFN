<?php

namespace PlantPath\Bundle\VDIFNBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VDIFNDailyImportCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        // Default date is today.
        $date = new \DateTime();

        $this
            ->setName('vdifn:daily-import')
            ->setDescription('Download & import a day of NOAA data into VDIFN')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Specify a date for which to download NOAA data (Format: Ymd)', $date->format('Ymd'));
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $console = $this->getApplication();
        $ymd = $input->getOption('date');
        $hours = $this->getContainer()->getParameter('vdifn.noaa_hours');

        foreach ($hours as $hour) {
            $hour = str_pad((string) $hour, 2, '0', STR_PAD_LEFT);
            $filepath = sprintf($this->getContainer()->getParameter('vdifn.noaa_path'), $ymd, $hour);

            $console->find('vdifn:download')->run(new ArrayInput([
                'command' => 'vdifn:download',
                '--date' => $ymd,
                '--hour' => $hour,
            ]), $output);

            $command = $console->find('vdifn:split');
            $fields = $command->filterFields($this->getContainer()->getParameter('vdifn.noaa_fields'));

            $command->run(new ArrayInput([
                'command' => 'vdifn:split',
                'file' => $filepath,
                '--fields' => $fields,
                '--remove' => true,
            ]), $output);

            $baseFilepath = $filepath;

            foreach ($fields as $field) {
                $filepath = $baseFilepath . '.' . $field;
                $csv = $filepath . '.csv';

                $console->find('vdifn:extract-to-csv')->run(new ArrayInput([
                    'command' => 'vdifn:extract-to-csv',
                    'file' => $filepath,
                    'csv' => $csv,
                    '--remove' => true,
                ]), $output);

                // $console->find('vdifn:import-from-csv')->run(new ArrayInput([
                //     'command' => 'vdifn:import-from-csv',
                //     'file' => $csv,
                // ]), $output);

            }
        }

        $console->find('vdifn:daily-backup')->run(new ArrayInput([
            'command' => 'vdifn:daily-backup',
            '--date' => $ymd,
            '--remove' => true
        ]), $output);
    }
}
