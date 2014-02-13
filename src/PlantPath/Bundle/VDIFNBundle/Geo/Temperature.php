<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class Temperature
{
    const KELVIN = 0;
    const CELSIUS = 1;

    /**
     * @var float
     */
    protected $temperature;

    /**
     * @var integer One of the scale constants.
     */
    protected $scale;

    public static function create($temperature, $scale = self::KELVIN)
    {
        return new static($temperature, $scale);
    }

    /**
     * Constructor.
     *
     * @param float $temperature
     * @param integer $scale One of the scale constants.
     */
    public function __construct($temperature, $scale = self::KELVIN)
    {
        $this
            ->setTemperature($temperature)
            ->setScale($scale);
    }

    /**
     * Returns a new Temperature object converted to a different scale.
     *
     * @param  integer $scale One of the scale constants.
     *
     * @return PlantPath\Bundle\VDIFNBundle\Geo\Temperature
     */
    public function convert($scale = self::KELVIN)
    {
        if ($this->scale === $scale) {
            return clone $this;
        }

        if ($this->scale === self::KELVIN && $scale === self::CELSIUS) {
            return new static($this->getTemperature() - 273.15, $scale);
        }

        throw new \RuntimeException('Unable to convert temperature: Unsupported scale');
    }

    /**
     * Gets the value of temperature.
     *
     * @return float
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * Sets the value of temperature.
     *
     * @param float $temperature the temperature
     *
     * @return self
     */
    public function setTemperature($temperature)
    {
        if (false === $temperature = filter_var($temperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate temperature as a float');
        }

        $this->temperature = $temperature;

        return $this;
    }

    /**
     * Gets the value of scale.
     *
     * @return integer One of the scale constants.
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * Sets the value of scale.
     *
     * @param integer One of the scale constants. $scale the scale
     *
     * @return self
     */
    public function setScale($scale)
    {
        if (
            $scale !== self::KELVIN &&
            $scale !== self::CELSIUS
        ) {
            throw new \InvalidArgumentException('Not a valid temperature scale');
        }

        $this->scale = $scale;

        return $this;
    }
}
