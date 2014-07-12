<?php

namespace PlantPath\Bundle\VDIFNBundle\Entity\Weather\Observed;

use Doctrine\ORM\Mapping as ORM;

/**
 * Observed hourly weather data.
 *
 * @ORM\Entity(repositoryClass="PlantPath\Bundle\VDIFNBundle\Repository\Weather\Observed\HourlyRepository")
 * @ORM\Table(
 *     name="weather_observed_hourly",
 *     indexes={
 *         @ORM\Index(name="usaf_wban_time_idx", columns={"usaf", "wban", "time"})
 *     }
 * )
 */
class Hourly
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="usaf", type="text")
     */
    protected $usaf;

    /**
     * @var string
     *
     * @ORM\Column(name="wban", type="text")
     */
    protected $wban;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time", type="utcdatetime")
     */
    protected $time;

    /**
     * @var float
     *
     * @ORM\Column(name="air_temperature", type="float", nullable=true)
     */
    protected $airTemperature;

    /**
     * @var float
     *
     * @ORM\Column(name="dew_point_temperature", type="float", nullable=true)
     */
    protected $dewPointTemperature;

    /**
     * @var float
     *
     * @ORM\Column(name="sea_level_pressure", type="float", nullable=true)
     */
    protected $seaLevelPressure;

    /**
     * @var integer
     *
     * @ORM\Column(name="wind_direction", type="smallint", nullable=true)
     */
    protected $windDirection;

    /**
     * @var float
     *
     * @ORM\Column(name="wind_speed_rate", type="float", nullable=true)
     */
    protected $windSpeedRate;

    /**
     * @var string
     *
     * @ORM\Column(name="sky_condition", type="text", nullable=true)
     */
    protected $skyCondition;

    /**
     * @var float
     *
     * @ORM\Column(name="precipitation_one_hour", type="float", nullable=true)
     */
    protected $precipitationOneHour;

    /**
     * @var float
     *
     * @ORM\Column(name="precipitation_six_hour", type="float", nullable=true)
     */
    protected $precipitationSixHour;

    /**
     * Factory.
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Weather\Hourly
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Creates new observed weather data based upon a time and station.
     *
     * @param  string   $usaf
     * @param  string   $wban
     * @param  DateTime $time
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Weather\Observed\Hourly
     */
    public static function createFromStationAndTime($usaf, $wban, \DateTime $time)
    {
        return static::create()
            ->setUsaf($usaf)
            ->setWban($wban)
            ->setTime($time);
    }

    /**
     * Gets the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the value of usaf.
     *
     * @return string
     */
    public function getUsaf()
    {
        return $this->usaf;
    }

    /**
     * Sets the value of usaf.
     *
     * @param string $usaf the usaf
     *
     * @return self
     */
    public function setUsaf($usaf)
    {
        $this->usaf = $usaf;

        return $this;
    }

    /**
     * Gets the value of wban.
     *
     * @return string
     */
    public function getWban()
    {
        return $this->wban;
    }

    /**
     * Sets the value of wban.
     *
     * @param string $wban the wban
     *
     * @return self
     */
    public function setWban($wban)
    {
        $this->wban = $wban;

        return $this;
    }

    /**
     * Gets the value of time.
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Sets the value of time.
     *
     * @param \DateTime $time the time
     *
     * @return self
     */
    public function setTime(\DateTime $time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Gets the value of airTemperature.
     *
     * @return float
     */
    public function getAirTemperature()
    {
        return $this->airTemperature;
    }

    /**
     * Sets the value of airTemperature.
     *
     * @param float $airTemperature the air temperature
     *
     * @return self
     */
    public function setAirTemperature($airTemperature)
    {
        if (null !== $airTemperature && false === $airTemperature = filter_var($airTemperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate air temperature as a float: ' . $airTemperature);
        }

        $this->airTemperature = $airTemperature;

        return $this;
    }

    /**
     * Gets the value of dewPointTemperature.
     *
     * @return float
     */
    public function getDewPointTemperature()
    {
        return $this->dewPointTemperature;
    }

    /**
     * Sets the value of dewPointTemperature.
     *
     * @param float $dewPointTemperature the dew point temperature
     *
     * @return self
     */
    public function setDewPointTemperature($dewPointTemperature)
    {
        if (null !== $dewPointTemperature && false === $dewPointTemperature = filter_var($dewPointTemperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate dew point temperature as a float: ' . $dewPointTemperature);
        }

        $this->dewPointTemperature = $dewPointTemperature;

        return $this;
    }

    /**
     * Gets the value of seaLevelPressure.
     *
     * @return float
     */
    public function getSeaLevelPressure()
    {
        return $this->seaLevelPressure;
    }

    /**
     * Sets the value of seaLevelPressure.
     *
     * @param float $seaLevelPressure the sea level pressure
     *
     * @return self
     */
    public function setSeaLevelPressure($seaLevelPressure)
    {
        if (null !== $seaLevelPressure && false === $seaLevelPressure = filter_var($seaLevelPressure, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate sea level pressure as a float: ' . $seaLevelPressure);
        }

        $this->seaLevelPressure = $seaLevelPressure;

        return $this;
    }

    /**
     * Gets the value of windDirection.
     *
     * @return integer
     */
    public function getWindDirection()
    {
        return $this->windDirection;
    }

    /**
     * Sets the value of windDirection.
     *
     * @param integer $windDirection the wind direction
     *
     * @return self
     */
    public function setWindDirection($windDirection)
    {
        if (null !== $windDirection && false === $windDirection = filter_var($windDirection, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Cannot validate wind direction as an integer: ' . $windDirection);
        }

        $this->windDirection = $windDirection;

        return $this;
    }

    /**
     * Gets the value of windSpeedRate.
     *
     * @return float
     */
    public function getWindSpeedRate()
    {
        return $this->windSpeedRate;
    }

    /**
     * Sets the value of windSpeedRate.
     *
     * @param float $windSpeedRate the wind speed rate
     *
     * @return self
     */
    public function setWindSpeedRate($windSpeedRate)
    {
        if (null !== $windSpeedRate && false === $windSpeedRate = filter_var($windSpeedRate, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate wind speed rate as a float: ' . $windSpeedRate);
        }

        $this->windSpeedRate = $windSpeedRate;

        return $this;
    }

    /**
     * Gets the value of skyCondition.
     *
     * @return string
     */
    public function getSkyCondition()
    {
        return $this->skyCondition;
    }

    /**
     * Sets the value of skyCondition.
     *
     * @param string $skyCondition the sky condition
     *
     * @return self
     */
    public function setSkyCondition($skyCondition)
    {
        $this->skyCondition = $skyCondition;

        return $this;
    }

    /**
     * Gets the value of precipitationOneHour.
     *
     * @return float
     */
    public function getPrecipitationOneHour()
    {
        return $this->precipitationOneHour;
    }

    /**
     * Sets the value of precipitationOneHour.
     *
     * @param float $precipitationOneHour the precipitation one hour
     *
     * @return self
     */
    public function setPrecipitationOneHour($precipitationOneHour)
    {
        if (null !== $precipitationOneHour && false === $precipitationOneHour = filter_var($precipitationOneHour, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate one-hour precipitation as a float: ' . $precipitationOneHour);
        }

        $this->precipitationOneHour = $precipitationOneHour;

        return $this;
    }

    /**
     * Gets the value of precipitationSixHour.
     *
     * @return float
     */
    public function getPrecipitationSixHour()
    {
        return $this->precipitationSixHour;
    }

    /**
     * Sets the value of precipitationSixHour.
     *
     * @param float $precipitationSixHour the precipitation six hour
     *
     * @return self
     */
    public function setPrecipitationSixHour($precipitationSixHour)
    {
        if (null !== $precipitationSixHour && false === $precipitationSixHour = filter_var($precipitationSixHour, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate six-hour precipitation as a float: ' . $precipitationSixHour);
        }

        $this->precipitationSixHour = $precipitationSixHour;

        return $this;
    }
}
