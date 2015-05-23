<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

use Doctrine\Common\Persistence\ObjectManager;
use PlantPath\Bundle\VDIFNBundle\Geo\Point;

interface DiseaseModelInterface
{
    /**
     * Applies the model to a given daily mean temp and leaf wetting time.
     *
     * @param int $meanTemperature
     * @param int $leafWettingTime
     *
     * @return int The disease severity value.
     */
    static function apply($meanTemperature, $leafWettingTime);

    /**
     * Return the appropriate threshold according to given factors in $data.
     *
     * @param DiseaseModelData $data The data needed to calculate the appropriate threshold.
     */
    static function determineThreshold(DiseaseModelData $data);

    /**
     * Return a structured array containing information about the thresholds of
     * this disease.
     *
     * @return array
     */
    static function getThresholds();

    /**
     * Get data for a single station by its USAF and WBAN.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $em
     * @param string $usaf
     * @param string $wban
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return array
     */
    static function getStationData(ObjectManager $em, $usaf, $wban, \DateTime $start, \DateTime $end);

    /**
     * Get data for a single point by its location and start and end date.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $em
     * @param \PlantPath\Bundle\VDIFNBundle\Geo\Point $point
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return \PlantPath\Bundle\VDIFNBundle\Entity\Weather\Daily
     */
    static function getPointData(ObjectManager $em, Point $point, \DateTime $start, \DateTime $end);

    /**
     * Return an array of data by a start and end date.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $em
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return array
     */
    static function getDataByDateRange(ObjectManager $em, \DateTime $start, \DateTime $end);
}
