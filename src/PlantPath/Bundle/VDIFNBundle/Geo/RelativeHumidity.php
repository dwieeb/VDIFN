<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class RelativeHumidity
{
    const CONSTANT_1 = 17.625;
    const CONSTANT_2 = 243.04;

    /**
     * @var float
     */
    protected $relativeHumidity;

    /**
     * Factory.
     *
     * @param  float $temperature
     * @param  float $dewPoint
     *
     * @return self
     */
    public static function createFromTemperatureAndDewPoint($temperature, $dewPoint)
    {
        $numerator = exp((self::CONSTANT_1 * $dewPoint) / (self::CONSTANT_2 + $dewPoint));
        $denominator = exp((self::CONSTANT_1 * $temperature) / (self::CONSTANT_2 + $temperature));
        $relativeHumidity = 100 * ($numerator / $denominator);

        $relativeHumidity = min($relativeHumidity, 100);
        $relativeHumidity = max($relativeHumidity, 0);

        return new self($relativeHumidity);
    }

    /**
     * Constructor.
     *
     * @param float $relativeHumidity
     */
    public function __construct($relativeHumidity)
    {
        $this->setRelativeHumidity($relativeHumidity);
    }

    /**
     * Gets the value of relativeHumidity.
     *
     * @return float
     */
    public function getRelativeHumidity()
    {
        return $this->relativeHumidity;
    }

    /**
     * Sets the value of relativeHumidity.
     *
     * @param float $relativeHumidity the relativeHumidity
     *
     * @return self
     */
    public function setRelativeHumidity($relativeHumidity)
    {
        if (false === $relativeHumidity = filter_var($relativeHumidity, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate relativeHumidity as a float');
        }

        $this->relativeHumidity = $relativeHumidity;

        return $this;
    }
}
