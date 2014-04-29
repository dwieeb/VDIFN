<?php

namespace PlantPath\Bundle\VDIFNBundle\Command\VDIFN\Predicted;

use PlantPath\Bundle\VDIFNBundle\Entity\Weather\Daily as DailyWeather;
use PlantPath\Bundle\VDIFNBundle\Entity\Weather\Hourly as HourlyWeather;
use PlantPath\Bundle\VDIFNBundle\Geo\Point;
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
    protected $hourlyRepo;

    /**
     * @var Doctrine\Common\Persistence\ObjectRepository
     */
    protected $dailyRepo;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        // Default date is today.
        $date = new \DateTime();

        $this
            ->setName('vdifn:predicted:aggregate')
            ->setDescription('Aggregate existing hourly data and calculate and store disease severity values')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Specify a date for which to aggregate hourly data', $date->format('Ymd'));
    }

    /**
     * Given a day, return the beginning of it.
     *
     * @param  DateTime $day
     *
     * @return DateTime
     */
    protected function getBeginningOfDay(\DateTime $day)
    {
        $beginning = clone $day;
        $beginning->setTime(0, 0, 0);

        return $beginning;
    }

    /**
     * Given a day, return the end of it.
     *
     * @param  DateTime $day
     *
     * @return DateTime
     */
    protected function getEndOfDay(\DateTime $day)
    {
        $end = clone $day;
        $end->setTime(0, 0, 0);
        $end->modify('+1 day');

        return $end;
    }

    /**
     * Makes a new DateTime object based upon a given day and hour.
     *
     * @param  DateTime $day
     * @param  int      $hour
     *
     * @return DateTime
     */
    protected function getDailyHour(\DateTime $day, $hour)
    {
        $dt = clone $day;
        $dt->setTime($hour, 0, 0);

        return $dt;
    }

    /**
     * Get the distinct latitude/longitude locations of a given day.
     *
     * @param  DateTime $beginning
     * @param  DateTime $end
     *
     * @return array
     */
    protected function getLocations(\DateTime $day)
    {
        $beginning = $this->getBeginningOfDay($day);
        $end = $this->getEndOfDay($day);

        $locations = $this->hourlyRepo
            ->createQueryBuilder('h')
            ->select('h.latitude', 'h.longitude')
            ->groupBy('h.latitude', 'h.longitude')
            ->where('h.verificationTime BETWEEN :beginning AND :end')
            ->getQuery()
            ->setParameter('beginning', $beginning)
            ->setParameter('end', $end)
            ->execute();

        $points = [];

        foreach ($locations as $location) {
            $points[] = new Point($location['latitude'], $location['longitude']);
        }

        return $points;
    }

    /**
     * Get the hourly weather data of a given location and day.
     *
     * @param  Point    $point
     * @param  DateTime $day
     *
     * @return array
     */
    protected function getHourlyWeather(Point $point, \DateTime $day)
    {
        $beginning = $this->getBeginningOfDay($day);
        $end = $this->getEndOfDay($day);

        $hourlies = $this->hourlyRepo
                ->createQueryBuilder('h')
                ->where('h.latitude = :latitude')
                ->andWhere('h.longitude = :longitude')
                ->andWhere('h.verificationTime BETWEEN :beginning AND :end')
                ->getQuery()
                ->setParameter('latitude', $point->getLatitude())
                ->setParameter('longitude', $point->getLongitude())
                ->setParameter('beginning', $beginning)
                ->setParameter('end', $end)
                ->getResult();

        if (9 !== count($hourlies)) {
            throw new \UnexpectedValueException('Not enough hourly data to aggregate this day: ' . $day->format('c'));
        }

        $hourlyObjects = [];

        foreach ($hourlies as $hourly) {
            $hourlyObjects[$hourly->getVerificationTime()->format('c')] = $hourly;
        }

        ksort($hourlyObjects);

        return $hourlyObjects;
    }

    /**
     * Get the hourly weather data of a given location and day and interpolate
     * values for missing hours.
     *
     * @param  Point    $point
     * @param  DateTime $day
     *
     * @return array
     */
    protected function getInterpolatedHourlyWeather(Point $point, \DateTime $day)
    {
        $beginning = $this->getBeginningOfDay($day);
        $end = $this->getEndOfDay($day);

        $hourlies = $this->getHourlyWeather($point, $day);

        foreach (new \DatePeriod($beginning, \DateInterval::createFromDateString('1 hour'), $end->modify('+1 second')) as $dt) {
            $hour = (int) $dt->format('H');

            if (0 !== $hour % 3) {
                $p = floor($hour / 3) * 3;
                $n = ceil($hour / 3) * 3;
                $prev = $hourlies[$this->getDailyHour($dt, $p)->format('c')];
                $next = $hourlies[$this->getDailyHour($dt, $n)->format('c')];
                $hourly = new HourlyWeather();
                $hourly->setVerificationTime($dt);
                $hourly->setTemperature($prev->getTemperature() + ($next->getTemperature() - $prev->getTemperature()) * (($hour - $p) / ($n - $p)));
                $hourly->setRelativeHumidity(round($prev->getRelativeHumidity() + ($next->getRelativeHumidity() - $prev->getRelativeHumidity()) * (($hour - $p) / ($n - $p))));
                $hourlies[$dt->format('c')] = $hourly;
            }
        }

        ksort($hourlies);

        return $hourlies;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');
        $threshold = (int) $this->getContainer()->getParameter('vdifn.lwt_rh_threshold');
        $ymd = $input->getOption('date');
        $date = new \DateTime($ymd);

        $this->em = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        // http://konradpodgorski.com/blog/2013/01/18/how-to-avoid-memory-leaks-in-symfony-2-commands
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->hourlyRepo = $this->em->getRepository('PlantPathVDIFNBundle:Weather\Hourly');
        $this->dailyRepo = $this->em->getRepository('PlantPathVDIFNBundle:Weather\Daily');

        $logger->info('Starting aggregation.', ['date' => $date->format('c')]);

        foreach ($this->getLocations($date) as $point) {
            $hourlies = $this->getInterpolatedHourlyWeather($point, $date);

            array_pop($hourlies);

            $leafWettingTime = 0;
            $sumTemperature = 0;

            foreach ($hourlies as $hourly) {
                if ($hourly->getRelativeHumidity() > $threshold) {
                    $leafWettingTime += 1;
                }

                $sumTemperature += $hourly->getTemperature();
            }

            $meanTemperature = $sumTemperature / count($hourlies);

            $daily = $this->dailyRepo->getOneBySpaceTime($date, $point) ?: DailyWeather::create();

            $daily
                ->setTime($date)
                ->setPoint($point)
                ->setMeanTemperature($meanTemperature)
                ->setLeafWettingTime($leafWettingTime)
                ->computeDsv();

            $this->em->persist($daily);

            // Remove hourly data once daily data is aggregated and persisted.
            foreach ($hourlies as $hourly) {
                $this->em->remove($hourly);
            }
        }

        $this->em->flush();
        $this->em->clear();

        $logger->info('Finished aggregation.');
    }
}
