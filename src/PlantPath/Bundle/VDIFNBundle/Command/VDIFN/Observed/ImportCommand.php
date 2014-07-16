<?php

namespace PlantPath\Bundle\VDIFNBundle\Command\VDIFN\Observed;

use PlantPath\Bundle\VDIFNBundle\Entity\Weather\Observed\Hourly as HourlyWeather;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    protected $filepath;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        // Default date is today.
        $date = new \DateTime();

        $this
            ->setName('vdifn:observed:import')
            ->setDescription('Import a data file from NOAA for a specific year')
            ->addArgument('usaf', InputArgument::REQUIRED, 'The USAF value that identifies the station')
            ->addArgument('wban', InputArgument::REQUIRED, 'The WBAN value that identifies the station')
            ->addOption('year', 'y', InputOption::VALUE_REQUIRED, 'Specify the year to import (Format: Y)', $date->format('Y'));
    }

    protected function readFile()
    {
        $this->logger->info('Gunzipping data file.', ['filepath' => $this->filepath]);
        $fh = gzopen($this->filepath, 'r');
        $this->logger->info('Starting import.');

        $i = 0;
        while (!feof($fh)) {
            $line = fgets($fh);

            $year = $this->getLineValue($line, 0, 4);
            $month = $this->getLineValue($line, 5, 2);
            $day = $this->getLineValue($line, 8, 2);
            $hour = $this->getLineValue($line, 11, 2);
            $time = \DateTime::createFromFormat('YmdH', $year . $month . $day . $hour);

            if (false === $time) {
                $this->logger->error('Error parsing time.', ['year' => $year, 'month' => $month, 'day' => $day, 'hour' => $hour]);
            }

            if (null === $hourlyData = $this->repo->getOneByStationAndTime($this->usaf, $this->wban, $time)) {
                $hourlyData = HourlyWeather::createFromStationAndTime($this->usaf, $this->wban, $time);
            }

            // As per ftp://ftp.ncdc.noaa.gov/pub/data/noaa/isd-lite/isd-lite-format.txt
            $hourlyData
                ->setAirTemperature($this->getLineValue($line, 13, 6, 10, '-9999'))
                ->setDewPointTemperature($this->getLineValue($line, 19, 5, 10, '-9999'))
                ->setSeaLevelPressure($this->getLineValue($line, 25, 6, 10, '-9999'))
                ->setWindDirection($this->getLineValue($line, 31, 6, 1, '-9999'))
                ->setWindSpeedRate($this->getLineValue($line, 37, 6, 10, '-9999'))
                ->setSkyCondition($this->getLineValue($line, 43, 6, null, '-9999'))
                ->setPrecipitationOneHour($this->getLineValue($line, 49, 6, 10, '-9999'))
                ->setPrecipitationSixHour($this->getLineValue($line, 55, 6, 10, '-9999'))
                ->setRelativeHumidity($hourlyData->calculateRelativeHumidity());

            // $this->logger->debug('Calculated relative humidity.', [
            //     'temperature' => $hourlyData->getAirTemperature(),
            //     'dewPointTemperature' => $hourlyData->getDewPointTemperature(),
            //     'relativeHumidity' => $hourlyData->getRelativeHumidity(),
            // ]);

            $this->em->persist($hourlyData);

            if ($i++ % 100) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        fclose($fh);
    }

    protected function getLineValue($line, $start, $length, $scale = null, $missingValue = null)
    {
        $value = trim(substr($line, $start, $length));

        if (null !== $missingValue && $missingValue === $value) {
            return null;
        }

        if (null !== $scale) {
            $value = filter_var($value, FILTER_VALIDATE_INT);
            return $value / $scale;
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger = $this->getContainer()->get('logger');
        $this->usaf = $input->getArgument('usaf');
        $this->wban = $input->getArgument('wban');
        $this->year = $input->getOption('year');
        $this->filepath = sprintf($this->getContainer()->getParameter('vdifn.noaa.observed.path.data_file'), $this->year, $this->usaf, $this->wban);

        $this->em = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        // http://konradpodgorski.com/blog/2013/01/18/how-to-avoid-memory-leaks-in-symfony-2-commands
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->repo = $this->em->getRepository('PlantPathVDIFNBundle:Weather\Observed\Hourly');

        $this->readFile();
        $this->em->flush();
        $this->em->clear();

        $this->logger->info('Finished import.');
    }
}
