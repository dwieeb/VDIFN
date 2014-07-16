<?php

namespace PlantPath\Bundle\VDIFNBundle\Command\VDIFN\Observed;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class DailyImportCommand extends ContainerAwareCommand
{
    /**
     * @var Doctrine\Common\Persistence\ObjectManager
     */
    protected $em;

    /**
     * @var Doctrine\Common\Persistence\ObjectRepository
     */
    protected $repo;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        // Default date is today.
        $date = new \DateTime();

        $this
            ->setName('vdifn:observed:daily-import')
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

        $logger->info('Starting daily import for observed data.');

        $years = array_unique(array_map(function($element) {
            return substr($element, 0, 4);
        }, $input->getArgument('date')));

        $this->em = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        $stations = $this->em
            ->getRepository('PlantPathVDIFNBundle:Station')
            ->getOpenByCountryAndState('US', 'WI');

        $station = [
            'usaf' => '010010',
            'wban' => '99999'
        ];

        foreach ($stations as $station) {
            foreach ($years as $year) {
                $usaf = $station['usaf'];
                $wban = $station['wban'];
                $exitCode = $console->find('vdifn:observed:download')->run(new ArrayInput([
                    'command' => 'vdifn:observed:download',
                    'usaf' => $usaf,
                    'wban' => $wban,
                    '--year' => $year,
                ]), $output);

                if ($exitCode === 0) {
                    $console->find('vdifn:observed:import')->run(new ArrayInput([
                        'command' => 'vdifn:observed:import',
                        'usaf' => $usaf,
                        'wban' => $wban,
                        '--year' => $year,
                    ]), $output);
                }
            }
        }

        $logger->info('Finished daily import.');

        $body = $this->getContainer()->get('templating')->render('PlantPathVDIFNBundle:Command:DailyImport/finished.html.twig', [
            'time' => $startTime->diff(new \DateTime()),
        ]);

        $message = \Swift_Message::newInstance()
            ->setSubject('VDIFN Daily Import for Observed Data Finished')
            ->setFrom($this->getContainer()->getParameter('vdifn.admin.email'))
            ->setTo($this->getContainer()->getParameter('vdifn.admin.emails'))
            ->setBody($body, 'text/html');

        $this->getContainer()->get('mailer')->send($message);
    }
}
