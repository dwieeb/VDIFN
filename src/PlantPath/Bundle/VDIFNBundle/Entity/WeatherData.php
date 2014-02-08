<?php

namespace PlantPath\Bundle\VDIFNBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WeatherData
 *
 * @ORM\Table(
 *     name="weather_data",
 *     indexes={
 *         @ORM\Index(name="time_location_dsv_idx", columns={"referenceTime", "latitude", "longitude", "dsv"})
 *     }
 * )
 * @ORM\Entity
 */
class WeatherData
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="referenceTime", type="datetime")
     */
    protected $referenceTime;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float")
     */
    protected $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float")
     */
    protected $longitude;

    /**
     * @var integer
     *
     * @ORM\Column(name="dsv", type="smallint")
     */
    protected $dsv;

    /**
     * @var float
     *
     * @ORM\Column(name="temperature", type="float")
     */
    protected $temperature;

    /**
     * @var float
     *
     * @ORM\Column(name="specificHumidity", type="float")
     */
    protected $specificHumidity;

    /**
     * @var float
     *
     * @ORM\Column(name="dewPointTemperature", type="float")
     */
    protected $dewPointTemperature;

    /**
     * @var integer
     *
     * @ORM\Column(name="relativeHumiditity", type="smallint")
     */
    protected $relativeHumiditity;

    /**
     * @var float
     *
     * @ORM\Column(name="totalPrecipitation", type="float")
     */
    protected $totalPrecipitation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="categoricalRain", type="boolean")
     */
    protected $categoricalRain;

    /**
     * @var integer
     *
     * @ORM\Column(name="totalCloudCover", type="smallint")
     */
    protected $totalCloudCover;

    /**
     * @var float
     *
     * @ORM\Column(name="surfaceTemperature", type="float")
     */
    protected $surfaceTemperature;

    /**
     * @var float
     *
     * @ORM\Column(name="precipitationRate", type="float")
     */
    protected $precipitationRate;

    /**
     * @var float
     *
     * @ORM\Column(name="windSpeed", type="float")
     */
    protected $windSpeed;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set referenceTime
     *
     * @param \DateTime $referenceTime
     *
     * @return WeatherData
     */
    public function setReferenceTime(\DateTime $referenceTime)
    {
        $this->referenceTime = $referenceTime;

        return $this;
    }

    /**
     * Get referenceTime
     *
     * @return \DateTime
     */
    public function getReferenceTime()
    {
        return $this->referenceTime;
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     *
     * @return WeatherData
     */
    public function setLatitude($latitude)
    {
        if (false === $latitude = filter_var($latitude, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Could not validate latitude as a float');
        }

        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     *
     * @return WeatherData
     */
    public function setLongitude($longitude)
    {
        if (false === $longitude = filter_var($longitude, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Could not validate longitude as a float');
        }

        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set dsv
     *
     * @param integer $dsv
     *
     * @return WeatherData
     */
    public function setDsv($dsv)
    {
        if (false === $dsv = filter_var($dsv, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Could not validate DSV as an integer');
        }

        $this->dsv = $dsv;

        return $this;
    }

    /**
     * Get dsv
     *
     * @return integer
     */
    public function getDsv()
    {
        return $this->dsv;
    }

    /**
     * Set temperature
     *
     * @param float $temperature
     *
     * @return WeatherData
     */
    public function setTemperature($temperature)
    {
        if (false === $temperature = filter_var($temperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Could not validate temperature as a float');
        }

        $this->temperature = $temperature;

        return $this;
    }

    /**
     * Get temperature
     *
     * @return float
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * Set specificHumidity
     *
     * @param float $specificHumidity
     *
     * @return WeatherData
     */
    public function setSpecificHumidity($specificHumidity)
    {
        if (false === $specificHumidity = filter_var($specificHumidity, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Could not validate specific humidity as a float');
        }

        $this->specificHumidity = $specificHumidity;

        return $this;
    }

    /**
     * Get specificHumidity
     *
     * @return float
     */
    public function getSpecificHumidity()
    {
        return $this->specificHumidity;
    }

    /**
     * Set dewPointTemperature
     *
     * @param float $dewPointTemperature
     *
     * @return WeatherData
     */
    public function setDewPointTemperature($dewPointTemperature)
    {
        if (false === $dewPointTemperature = filter_var($dewPointTemperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Could not validate dew point temperature as a float');
        }

        $this->dewPointTemperature = $dewPointTemperature;

        return $this;
    }

    /**
     * Get dewPointTemperature
     *
     * @return float
     */
    public function getDewPointTemperature()
    {
        return $this->dewPointTemperature;
    }

    /**
     * Set relativeHumiditity
     *
     * @param integer $relativeHumiditity
     *
     * @return WeatherData
     */
    public function setRelativeHumiditity($relativeHumiditity)
    {
        if (false === $relativeHumiditity = filter_var($relativeHumiditity, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Could not validate relative humidity as an integer');
        }

        $this->relativeHumiditity = $relativeHumiditity;

        return $this;
    }

    /**
     * Get relativeHumiditity
     *
     * @return integer
     */
    public function getRelativeHumiditity()
    {
        return $this->relativeHumiditity;
    }

    /**
     * Set totalPrecipitation
     *
     * @param float $totalPrecipitation
     *
     * @return WeatherData
     */
    public function setTotalPrecipitation($totalPrecipitation)
    {
        if (false === $totalPrecipitation = filter_var($totalPrecipitation, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Could not validate total percipitation as a float');
        }

        $this->totalPrecipitation = $totalPrecipitation;

        return $this;
    }

    /**
     * Get totalPrecipitation
     *
     * @return float
     */
    public function getTotalPrecipitation()
    {
        return $this->totalPrecipitation;
    }

    /**
     * Set categoricalRain
     *
     * @param boolean $categoricalRain
     *
     * @return WeatherData
     */
    public function setCategoricalRain($categoricalRain)
    {
        if (null === $categoricalRain = filter_var($categoricalRain, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
            throw new \InvalidArgumentException('Could not validate categorical rain as a boolean');
        }

        $this->categoricalRain = $categoricalRain;

        return $this;
    }

    /**
     * Get categoricalRain
     *
     * @return boolean
     */
    public function getCategoricalRain()
    {
        return $this->categoricalRain;
    }

    /**
     * Set totalCloudCover
     *
     * @param integer $totalCloudCover
     *
     * @return WeatherData
     */
    public function setTotalCloudCover($totalCloudCover)
    {
        if (null === $totalCloudCover = filter_var($totalCloudCover, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Could not validate total cloud cover as an integer');
        }

        $this->totalCloudCover = $totalCloudCover;

        return $this;
    }

    /**
     * Get totalCloudCover
     *
     * @return integer
     */
    public function getTotalCloudCover()
    {
        return $this->totalCloudCover;
    }

    /**
     * Set surfaceTemperature
     *
     * @param float $surfaceTemperature
     *
     * @return WeatherData
     */
    public function setSurfaceTemperature($surfaceTemperature)
    {
        if (null === $surfaceTemperature = filter_var($surfaceTemperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Could not validate surface temperature as a float');
        }

        $this->surfaceTemperature = $surfaceTemperature;

        return $this;
    }

    /**
     * Get surfaceTemperature
     *
     * @return float
     */
    public function getSurfaceTemperature()
    {
        return $this->surfaceTemperature;
    }

    /**
     * Set precipitationRate
     *
     * @param float $precipitationRate
     *
     * @return WeatherData
     */
    public function setPrecipitationRate($precipitationRate)
    {
        if (null === $precipitationRate = filter_var($precipitationRate, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Could not validate precipitation rate as a float');
        }

        $this->precipitationRate = $precipitationRate;

        return $this;
    }

    /**
     * Get precipitationRate
     *
     * @return float
     */
    public function getPrecipitationRate()
    {
        return $this->precipitationRate;
    }

    /**
     * Set windSpeed
     *
     * @param float $windSpeed
     *
     * @return WeatherData
     */
    public function setWindSpeed($windSpeed)
    {
        if (null === $windSpeed = filter_var($windSpeed, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Could not validate wind speed as a float');
        }

        $this->windSpeed = $windSpeed;

        return $this;
    }

    /**
     * Get windSpeed
     *
     * @return float
     */
    public function getWindSpeed()
    {
        return $this->windSpeed;
    }
}
