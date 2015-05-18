<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

interface DiseaseModelInterface
{
    /**
     * Returns the disease severity matrix common in disease models.
     *
     * @return array
     */
    static function getMatrix();

    /**
     * Applies the model to a given daily mean temp and leaf wetting time.
     *
     * @param int $meanTemperature
     * @param int $leafWettingTime
     *
     * @return int The disease severity value.
     */
    static function apply($meanTemperature, $leafWettingTime);
}
