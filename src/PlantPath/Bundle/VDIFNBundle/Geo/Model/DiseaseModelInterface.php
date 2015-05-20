<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

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
}
