<?php

namespace PlantPath\Bundle\VDIFNBundle\Command\VDIFN\Predicted;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DailyImportCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        // Default date is today.
        $date = new \DateTime();

        $this
            ->setName('vdifn:predicted:daily-import')
            ->setDescription('Download & import a day or multiple days of NOAA data into VDIFN')
            ->addArgument('date', InputArgument::IS_ARRAY, 'Specify date(s) for which to download NOAA data (Format: Ymd)', [$date->format('Ymd')]);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = new \DateTime();
        $console = $this->getApplication();
        $logger = $this->getContainer()->get('logger');
        $hours = $this->getContainer()->getParameter('vdifn.noaa.predicted.hours');

        $logger->info('Starting daily import for predicted data.');

        foreach ($input->getArgument('date') as $ymd) {
            foreach ($hours as $hour) {
                $hour = str_pad((string) $hour, 2, '0', STR_PAD_LEFT);
                $filepath = sprintf($this->getContainer()->getParameter('vdifn.noaa.predicted.path.data_file'), $ymd, $hour);

                $console->find('vdifn:predicted:download')->run(new ArrayInput([
                    'command' => 'vdifn:predicted:download',
                    '--date' => $ymd,
                    '--hour' => $hour,
                ]), $output);

                $command = $console->find('vdifn:predicted:split');
                $fields = $command->filterFields($this->getContainer()->getParameter('vdifn.noaa.predicted.fields'));

                $command->run(new ArrayInput([
                    'command' => 'vdifn:predicted:split',
                    'file' => $filepath,
                    '--fields' => $fields,
                    '--remove' => true,
                ]), $output);

                $baseFilepath = $filepath;

                foreach ($fields as $field) {
                    $filepath = $baseFilepath . '.' . $field;
                    $csv = $filepath . '.csv';

                    $console->find('vdifn:predicted:extract-to-csv')->run(new ArrayInput([
                        'command' => 'vdifn:predicted:extract-to-csv',
                        'file' => $filepath,
                        'csv' => $csv,
                        '--remove' => true,
                    ]), $output);

                    $console->find('vdifn:predicted:import-from-csv')->run(new ArrayInput([
                        'command' => 'vdifn:predicted:import-from-csv',
                        'file' => $csv,
                    ]), $output);
                }
            }

            // There should be predictions for the next three days.
            foreach (new \DatePeriod(new \DateTime($ymd), \DateInterval::createFromDateString('1 day'), 2) as $day) {
                $console->find('vdifn:predicted:aggregate')->run(new ArrayInput([
                    'command' => 'vdifn:predicted:aggregate',
                    '--date' => $day->format('Ymd'),
                ]), $output);
            }

            $console->find('vdifn:predicted:daily-backup')->run(new ArrayInput([
                'command' => 'vdifn:predicted:daily-backup',
                '--date' => $ymd,
                '--remove' => true,
            ]), $output);
        }

        $logger->info('Finished daily import.');

        // $body = $this->getContainer()->get('templating')->render('PlantPathVDIFNBundle:Command:DailyImport/finished.html.twig', [
        //     'time' => $startTime->diff(new \DateTime()),
        // ]);

        // $message = \Swift_Message::newInstance()
        //     ->setSubject('VDIFN Daily Import for Predicted Data Finished')
        //     ->setFrom($this->getContainer()->getParameter('vdifn.admin.email'))
        //     ->setTo($this->getContainer()->getParameter('vdifn.admin.emails'))
        //     ->setBody($body, 'text/html');

        // $this->getContainer()->get('mailer')->send($message);
    }
}
