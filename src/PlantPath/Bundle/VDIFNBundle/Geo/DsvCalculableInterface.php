<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

interface DsvCalculableInterface
{
    /**
     * Set temperature
     *
     * @param float $temperature
     * @return self
     */
    public function setTemperature($temperature);

    /**
     * Get temperature
     *
     * @return float
     */
    public function getTemperature();

    /**
     * Set relativeHumidity
     *
     * @param integer $relativeHumidity
     * @return self
     */
    public function setRelativeHumidity($relativeHumidity);

    /**
     * Get relativeHumidity
     *
     * @return integer
     */
    public function getRelativeHumidity();
}
