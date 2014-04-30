<?php

namespace PlantPath\Bundle\VDIFNBundle\Entity\Weather;

use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use PlantPath\Bundle\VDIFNBundle\Geo\Temperature;
use Doctrine\ORM\Mapping as ORM;

/**
 * Hourly weather data.
 *
 * @ORM\Entity(repositoryClass="PlantPath\Bundle\VDIFNBundle\Repository\Weather\HourlyRepository")
 * @ORM\Table(
 *     name="weather_hourly",
 *     indexes={
 *         @ORM\Index(name="location_time_idx", columns={"latitude", "longitude", "verification_time"})
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
     * @var \DateTime
     *
     * @ORM\Column(name="reference_time", type="utcdatetime")
     */
    protected $referenceTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="verification_time", type="utcdatetime")
     */
    protected $verificationTime;

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
     * @var float
     *
     * @ORM\Column(name="temperature", type="float", nullable=true)
     */
    protected $temperature;

    /**
     * @var float
     *
     * @ORM\Column(name="specific_humidity", type="float", nullable=true)
     */
    protected $specificHumidity;

    /**
     * @var float
     *
     * @ORM\Column(name="dew_point_temperature", type="float", nullable=true)
     */
    protected $dewPointTemperature;

    /**
     * @var integer
     *
     * @ORM\Column(name="relative_humidity", type="smallint", nullable=true)
     */
    protected $relativeHumidity;

    /**
     * @var float
     *
     * @ORM\Column(name="total_precipitation", type="float", nullable=true)
     */
    protected $totalPrecipitation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="categorical_rain", type="boolean", nullable=true)
     */
    protected $categoricalRain;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_cloud_cover", type="smallint", nullable=true)
     */
    protected $totalCloudCover;

    /**
     * @var float
     *
     * @ORM\Column(name="surface_temperature", type="float", nullable=true)
     */
    protected $surfaceTemperature;

    /**
     * @var float
     *
     * @ORM\Column(name="precipitation_rate", type="float", nullable=true)
     */
    protected $precipitationRate;

    /**
     * @var float
     *
     * @ORM\Column(name="wind_speed", type="float", nullable=true)
     */
    protected $windSpeed;

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
     * Creates new weather data based upon a date and location.
     *
     * @param  DateTime $date
     * @param  Point    $point
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Weather\Hourly
     */
    public static function createFromSpaceTime(\DateTime $date, Point $point)
    {
        return static::create()
            ->setVerificationTime($date)
            ->setPoint($point);
    }

    /**
     * Set this weather data's latitude and longitude from a Point object.
     *
     * @param  PlantPath\Bundle\VDIFNBundle\Geo\Point $point
     *
     * @return self
     */
    public function setPoint(Point $point)
    {
        $this
            ->setLatitude($point->getLatitude())
            ->setLongitude($point->getLongitude());

        return $this;
    }

    /**
     * Set a value in this class based upon a field's parameter and level,
     * which can be found in the inventory of the file.
     *
     * @link http://www.nco.ncep.noaa.gov/pmb/products/nam/nam.t00z.awip1200.tm00.grib2.shtml
     *
     * @param  string $parameter
     * @param  string $level
     * @param  mixed  $value
     *
     * @return self
     */
    public function setParameter($parameter, $level, $value)
    {
        switch ($parameter) {
            case 'TMP':
                $value = Temperature::create($value, Temperature::KELVIN)->convert(Temperature::CELSIUS)->getTemperature();

                switch ($level) {
                    case '2 m above ground':
                        return $this->setTemperature($value);

                    case 'surface':
                        return $this->setSurfaceTemperature($value);
                }

                throw new \InvalidArgumentException('Unknown level/layer for parameter TMP: ' . $level);

            case 'SPFH':
                switch ($level) {
                    case '2 m above ground':
                        return $this->setSpecificHumidity($value);
                }

                throw new \InvalidArgumentException('Unknown level/layer for parameter SPFH: ' . $level);

            case 'DPT':
                $value = Temperature::create($value, Temperature::KELVIN)->convert(Temperature::CELSIUS)->getTemperature();

                switch ($level) {
                    case '2 m above ground':
                        return $this->setDewPointTemperature($value);
                }

                throw new \InvalidArgumentException('Unknown level/layer for parameter DPT: ' . $level);

            case 'RH':
                switch ($level) {
                    case '2 m above ground':
                        return $this->setRelativeHumidity($value);
                }

                throw new \InvalidArgumentException('Unknown level/layer for parameter RH: ' . $level);

            case 'APCP':
                switch ($level) {
                    case 'surface':
                        return $this->setTotalPrecipitation($value);
                }

                throw new \InvalidArgumentException('Unknown level/layer for parameter APCP: ' . $level);

            case 'CRAIN':
                switch ($level) {
                    case 'surface':
                        return $this->setCategoricalRain($value);
                }

                throw new \InvalidArgumentException('Unknown level/layer for parameter CRAIN: ' . $level);

            case 'TCDC':
                switch ($level) {
                    case 'entire atmosphere (considered as a single layer)':
                        return $this->setTotalCloudCover($value);
                }

                throw new \InvalidArgumentException('Unknown level/layer for parameter TCDC: ' . $level);

            case 'PRATE':
                switch ($level) {
                    case 'surface':
                        return $this->setPrecipitationRate($value);
                }

                throw new \InvalidArgumentException('Unknown level/layer for parameter PRATE: ' . $level);

            case 'GUST':
                switch ($level) {
                    case 'surface':
                        return $this->setWindSpeed($value);
                }

                throw new \InvalidArgumentException('Unknown level/layer for parameter GUST: ' . $level);
        }

        throw new \InvalidArgumentException('Unknown parameter: ' . $parameter);
    }

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
     * @return Hourly
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
     * Set verificationTime
     *
     * @param \DateTime $verificationTime
     * @return Hourly
     */
    public function setVerificationTime(\DateTime $verificationTime)
    {
        $this->verificationTime = $verificationTime;

        return $this;
    }

    /**
     * Get verificationTime
     *
     * @return \DateTime
     */
    public function getVerificationTime()
    {
        return $this->verificationTime;
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     * @return Hourly
     */
    public function setLatitude($latitude)
    {
        if (false === $latitude = filter_var($latitude, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate latitude as a float: ' . $latitude);
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
     * @return Hourly
     */
    public function setLongitude($longitude)
    {
        if (false === $longitude = filter_var($longitude, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate longitude as a float: ' . $longitude);
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
     * Set temperature
     *
     * @param float $temperature
     * @return Hourly
     */
    public function setTemperature($temperature)
    {
        if (false === $temperature = filter_var($temperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate temperature as a float: ' . $temperature);
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
     * @return Hourly
     */
    public function setSpecificHumidity($specificHumidity)
    {
        if (false === $specificHumidity = filter_var($specificHumidity, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate specific humidity as a float: ' . $specificHumidity);
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
     * @return Hourly
     */
    public function setDewPointTemperature($dewPointTemperature)
    {
        if (false === $dewPointTemperature = filter_var($dewPointTemperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate dew point temperature as a float: ' . $dewPointTemperature);
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
     * Set relativeHumidity
     *
     * @param integer $relativeHumidity
     * @return Hourly
     */
    public function setRelativeHumidity($relativeHumidity)
    {
        if (false === $relativeHumidity = filter_var($relativeHumidity, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Cannot validate relative humidity as an integer: ' . $relativeHumidity);
        }

        $this->relativeHumidity = $relativeHumidity;

        return $this;
    }

    /**
     * Get relativeHumidity
     *
     * @return integer
     */
    public function getRelativeHumidity()
    {
        return $this->relativeHumidity;
    }

    /**
     * Set totalPrecipitation
     *
     * @param float $totalPrecipitation
     * @return Hourly
     */
    public function setTotalPrecipitation($totalPrecipitation)
    {
        if (false === $totalPrecipitation = filter_var($totalPrecipitation, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate total precipitation as a float: ' . $totalPrecipitation);
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
     * @return Hourly
     */
    public function setCategoricalRain($categoricalRain)
    {
        if (null === $categoricalRain = filter_var($categoricalRain, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
            throw new \InvalidArgumentException('Cannot validate categorical rain as a boolean: ' . $categoricalRain);
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
     * @return Hourly
     */
    public function setTotalCloudCover($totalCloudCover)
    {
        if (false === $totalCloudCover = filter_var($totalCloudCover, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Cannot validate total cloud cover as an integer: ' . $totalCloudCover);
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
     * @return Hourly
     */
    public function setSurfaceTemperature($surfaceTemperature)
    {
        if (false === $surfaceTemperature = filter_var($surfaceTemperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate surface temperature as a float: ' . $surfaceTemperature);
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
     * @return Hourly
     */
    public function setPrecipitationRate($precipitationRate)
    {
        if (false === $precipitationRate = filter_var($precipitationRate, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate precipitation rate as a float: ' . $precipitationRate);
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
     * @return Hourly
     */
    public function setWindSpeed($windSpeed)
    {
        if (false === $windSpeed = filter_var($windSpeed, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate wind speed as a float: ' . $windSpeed);
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
