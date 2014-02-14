<?php

namespace PlantPath\Bundle\VDIFNBundle\Command;

use PlantPath\Bundle\VDIFNBundle\Entity\Weather\Hourly as HourlyWeather;
use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class VDIFNImportFromCsvCommand extends ContainerAwareCommand
{
    /**
     * @var array
     */
    protected $states;

    /**
     * @var PlantPath\Bundle\VDIFNBundle\Service\StateService
     */
    protected $service;

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
        $this
            ->setName('vdifn:import-from-csv')
            ->setDescription('Import a wgrib2-formatted CSV of a NOAA data file into the database')
            ->addArgument('file', InputArgument::REQUIRED, 'The file path to the CSV file')
            ->addOption('remove', 'r', InputOption::VALUE_NONE, 'Remove the CSV file after importing');
    }

    /**
     * Returns true if the latitude/longitude coordinate occurs within the list
     * of configured states.
     *
     * @param  PlantPath\Bundle\VDIFNBundle\Geo\Point $point
     *
     * @return boolean
     */
    protected function pointInStates(Point $point)
    {
        foreach ($this->states as $state) {
            if ($state->containsPoint($point)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Given a set of values, process this weather data. Determine whether or
     * not to persist.
     *
     * @param  DateTime $referenceTime
     * @param  DateTime $verificationTime
     * @param  PlantPath\Bundle\VDIFNBundle\Geo\Point $point
     * @param  string   $field
     * @param  string   $level
     * @param  string   $value
     */
    protected function processWeatherData(\DateTime $referenceTime, \DateTime $verificationTime, Point $point, $field, $level, $value)
    {
        if ($this->pointInStates($point)) {
            if (null === $hourlyData = $this->repo->getOneBySpaceTime($verificationTime, $point)) {
                $hourlyData = HourlyWeather::createFromSpaceTime($verificationTime, $point);
                $hourlyData->setReferenceTime($referenceTime);
            }

            $hourlyData->setParameter($field, $level, $value);

            $this->em->persist($hourlyData);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filepath = $input->getArgument('file');
        $tz = new \DateTimeZone('UTC');

        if (!file_exists($filepath)) {
            throw new \RuntimeException('File not found at: ' . $filepath);
        }

        $this->service = $this->getContainer()->get('vdifn.state');
        $this->states = [];

        foreach ($this->getContainer()->getParameter('vdifn.noaa_states') as $stateName) {
            $this->states[] = $this->service->getStateWithBoundaries($stateName);
        }

        $this->em = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        // http://konradpodgorski.com/blog/2013/01/18/how-to-avoid-memory-leaks-in-symfony-2-commands
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->repo = $this->em->getRepository('PlantPathVDIFNBundle:Weather\Hourly');

        $file = new \SplFileObject($filepath);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

        foreach ($file as $row) {
            if (false !== $row) {
                list($date1, $date2, $field, $level, $longitude, $latitude, $value) = $row;
                $this->processWeatherData(new \DateTime($date1, $tz), new \DateTime($date2, $tz), new Point($latitude, $longitude), $field, $level, $value);
            }
        }

        $this->em->flush();
        $this->em->clear();

        if ($input->getOption('remove')) {
            $fs = new Filesystem();
            $fs->remove($filepath);
        }
    }
}
