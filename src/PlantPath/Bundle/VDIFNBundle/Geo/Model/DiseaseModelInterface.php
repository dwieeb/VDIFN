<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

use Doctrine\Common\Persistence\ObjectManager;

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
