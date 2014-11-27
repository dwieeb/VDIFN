<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class DegreeDay
{
    /**
     * @var float
     */
    protected $baseTemperature;

    /**
     * @var float
     */
    protected $averageDailyTemperature;

    /**
     * Factory.
     *
     * @param float $baseTemperature
     * @param float $averageDailyTemperature
     *
     * @return self
     */
    public static function create($baseTemperature, $averageDailyTemperature)
    {
        return new self($baseTemperature, $averageDailyTemperature);
    }

    /**
     * Constructor.
     *
     * @param float $baseTemperature
     * @param float $averageDailyTemperature
     */
    public function __construct($baseTemperature, $averageDailyTemperature)
    {
        $this
            ->setBaseTemperature($baseTemperature)
            ->setAverageDailyTemperature($averageDailyTemperature);
    }

    public function calculate()
    {
        $difference = $this->getAverageDailyTemperature() - $this->getBaseTemperature();

        if ($difference <= 0) {
            return 0;
        }

        return $difference;
    }

    /**
     * Gets the value of baseTemperature.
     *
     * @return float
     */
    public function getBaseTemperature()
    {
        return $this->baseTemperature;
    }

    /**
     * Sets the value of baseTemperature.
     *
     * @param float $baseTemperature the base temperature
     *
     * @return self
     */
    public function setBaseTemperature($baseTemperature)
    {
        if (false === $baseTemperature = filter_var($baseTemperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate base temperature as a float');
        }

        $this->baseTemperature = $baseTemperature;

        return $this;
    }

    /**
     * Gets the value of averageDailyTemperature.
     *
     * @return float
     */
    public function getAverageDailyTemperature()
    {
        return $this->averageDailyTemperature;
    }

    /**
     * Sets the value of averageDailyTemperature.
     *
     * @param float $averageDailyTemperature the average daily temperature
     *
     * @return self
     */
    public function setAverageDailyTemperature($averageDailyTemperature)
    {
        if (false === $averageDailyTemperature = filter_var($averageDailyTemperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate average daily temperature as a float');
        }

        $this->averageDailyTemperature = $averageDailyTemperature;

        return $this;
    }
}
