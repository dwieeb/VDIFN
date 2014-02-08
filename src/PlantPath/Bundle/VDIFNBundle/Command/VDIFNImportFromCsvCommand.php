<?php

namespace PlantPath\Bundle\VDIFNBundle\Command;

use PlantPath\Bundle\VDIFNBundle\Entity\WeatherData;
use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        if (null === $this->service) {
            $this->service = $this->getContainer()->get('vdifn.state');
        }

        if (null === $this->states) {
            $this->states = [];

            foreach ($this->getContainer()->getParameter('vdifn.noaa_states') as $stateName) {
                $this->states[] = $this->service->getStateWithBoundaries($stateName);
            }
        }

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
     * @param  DateTime $date
     * @param  PlantPath\Bundle\VDIFNBundle\Geo\Point $point
     * @param  string   $field
     * @param  string   $level
     * @param  string   $value
     */
    protected function processWeatherData(\DateTime $date, Point $point, $field, $level, $value)
    {
        if ($this->pointInStates($point)) {
            $weatherData = $this->repo->getOneBySpaceTime($date, $point) ?: WeatherData::createFromSpaceTime($date, $point);
            $weatherData->setParameter($field, $level, $value);

            $this->em->persist($weatherData);
        }
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

        $this->em = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        $this->repo = $this->em->getRepository('PlantPathVDIFNBundle:WeatherData');

        $file = new \SplFileObject($filepath);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);

        foreach ($file as $row) {
            if (false !== $row) {
                list($date1, $date2, $field, $level, $longitude, $latitude, $value) = $row;
                $this->processWeatherData(new \DateTime($date1), new Point($latitude, $longitude), $field, $level, $value);
            }
        }

        $this->em->flush();

        if ($input->getOption('remove')) {
            unlink($filepath);
        }
    }
}
