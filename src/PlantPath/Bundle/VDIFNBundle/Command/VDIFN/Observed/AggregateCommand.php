<?php

namespace PlantPath\Bundle\VDIFNBundle\Command\VDIFN\Observed;

use PlantPath\Bundle\VDIFNBundle\Entity\Weather\Observed\Daily as DailyWeather;
use PlantPath\Bundle\VDIFNBundle\Entity\Weather\Observed\Hourly as HourlyWeather;
use PlantPath\Bundle\VDIFNBundle\Geo\DateUtils;
use PlantPath\Bundle\VDIFNBundle\Geo\Model\DiseaseModel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AggregateCommand extends ContainerAwareCommand
{
    /**
     * @var Doctrine\Common\Persistence\ObjectManager
     */
    protected $em;

    /**
     * @var Doctrine\Common\Persistence\ObjectRepository
     */
    protected $stationRepo;

    /**
     * @var Doctrine\Common\Persistence\ObjectRepository
     */
    protected $hourlyRepo;

    /**
     * @var Doctrine\Common\Persistence\ObjectRepository
     */
    protected $dailyRepo;

    protected $logger;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        // Default date is today.
        $date = new \DateTime();

        $this
            ->setName('vdifn:observed:aggregate')
            ->setDescription('Aggregate existing hourly data and calculate and store disease severity values')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Specify a date for which to aggregate hourly data', $date->format('Ymd'));
    }

    /**
     * Get the distinct stations.
     *
     * @param  string $country
     * @param  string $state
     *
     * @return array
     */
    protected function getStations($country, $state)
    {
        return $this->stationRepo
            ->createQueryBuilder('s')
            ->select('s.usaf', 's.wban')
            ->where('s.country = :country')
            ->andWhere('s.state = :state')
            ->andWhere('s.endTime IS NULL')
            ->setParameter('country', $country)
            ->setParameter('state', $state)
            ->getQuery()
            ->execute();
    }

    /**
     * Get the hourly weather data of a given station and day.
     *
     * @param  string   $usaf
     * @param  string   $wban
     * @param  DateTime $day
     *
     * @return array
     */
    protected function getHourlyWeather($usaf, $wban, \DateTime $day)
    {
        $beginning = DateUtils::getBeginningOfDay($day);
        $end = DateUtils::getEndOfDay($day)->modify('-1 second');

        $hourlies = $this->hourlyRepo
                ->createQueryBuilder('h')
                ->where('h.usaf = :usaf')
                ->andWhere('h.wban = :wban')
                ->andWhere('h.time BETWEEN :beginning AND :end')
                ->orderBy('h.time', 'ASC')
                ->getQuery()
                ->setParameter('usaf', $usaf)
                ->setParameter('wban', $wban)
                ->setParameter('beginning', $beginning)
                ->setParameter('end', $end)
                ->getResult();

        if (count($hourlies) <= 1) {
            throw new \UnexpectedValueException('Not enough hourly data to aggregate day.');
        }

        return $hourlies;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger = $this->getContainer()->get('logger');
        $threshold = (int) $this->getContainer()->getParameter('vdifn.lwt_rh_threshold');
        $ymd = $input->getOption('date');
        $date = new \DateTime($ymd);

        $this->em = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        // http://konradpodgorski.com/blog/2013/01/18/how-to-avoid-memory-leaks-in-symfony-2-commands
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->stationRepo = $this->em->getRepository('PlantPathVDIFNBundle:Station');
        $this->hourlyRepo = $this->em->getRepository('PlantPathVDIFNBundle:Weather\Observed\Hourly');
        $this->dailyRepo = $this->em->getRepository('PlantPathVDIFNBundle:Weather\Observed\Daily');

        $this->logger->info('Starting aggregation.', ['date' => $date->format('c')]);

        foreach ($this->getStations('US', 'WI') as $station) {
            try {
                $hourlies = $this->getHourlyWeather($station['usaf'], $station['wban'], $date);
            } catch (\UnexpectedValueException $ex) {
                $this->logger->error($ex->getMessage(), ['usaf' => $station['usaf'], 'wban' => $station['wban'], 'date' => $date->format('c')]);
                continue;
            }

            list($meanTemperature, $leafWettingTime) = DiseaseModel::calculateTemperatureAndLeafWettingTime($hourlies, $threshold);

            $daily = $this->dailyRepo->getOneByStationAndTime($station['usaf'], $station['wban'], $date) ?: DailyWeather::create();

            $daily
                ->setUsaf($station['usaf'])
                ->setWban($station['wban'])
                ->setTime($date)
                ->setMeanTemperature($meanTemperature)
                ->setLeafWettingTime($leafWettingTime)
                ->calculateDsv();

            $this->em->persist($daily);
        }

        $this->em->flush();
        $this->em->clear();

        $this->logger->info('Finished aggregation.');
    }
}
