<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

interface DailyWeatherInterface
{
    /**
     * Gets the value of meanTemperature.
     *
     * @return integer
     */
    function getMeanTemperature();

    /**
     * Sets the value of meanTemperature.
     *
     * @param integer $meanTemperature the mean temperature
     *
     * @return self
     */
    function setMeanTemperature($meanTemperature);

    /**
     * Gets the value of leafWettingTime.
     *
     * @return integer
     */
    function getLeafWettingTime();

    /**
     * Sets the value of leafWettingTime.
     *
     * @param integer $leafWettingTime the leaf wetting time
     *
     * @return self
     */
    function setLeafWettingTime($leafWettingTime);

    /**
     * Sets the current model so getDsv() can function for convenience.
     *
     * @param string $crop
     * @param string $infliction
     */
    function setCurrentModel($crop, $infliction);

    /**
     * Convenience method to get the current DSV.
     *
     * @return integer
     */
    function getDsv();

    /**
     * Get the DSV value specific to carrot foliar disease.
     *
     * @return integer
     */
    function getDsvCarrotFoliarDisease();

    /**
     * @param integer $dsvCarrotFoliarDisease
     */
    function setDsvCarrotFoliarDisease($dsvCarrotFoliarDisease);

    /**
     * Get the DSV value specific to potato late blight.
     *
     * @return integer
     */
    function getDsvPotatoLateBlight();

    /**
     * @param integer $dsvPotatoLateBlight
     */
    function setDsvPotatoLateBlight($dsvPotatoLateBlight);
}
