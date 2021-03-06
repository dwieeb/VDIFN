<?php

namespace PlantPath\Bundle\VDIFNBundle\Entity;

use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use PlantPath\Bundle\VDIFNBundle\Geo\Temperature;
use Doctrine\ORM\Mapping as ORM;

/**
 * Hourly weather data.
 *
 * @ORM\Entity(repositoryClass="PlantPath\Bundle\VDIFNBundle\Repository\StationRepository")
 * @ORM\Table(
 *     name="stations",
 *     indexes={
 *         @ORM\Index(name="usaf_wban_idx", columns={"usaf", "wban"}),
 *         @ORM\Index(name="country_state_idx", columns={"country", "state"})
 *     }
 * )
 */
class Station
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
     * @ORM\Column(name="usaf", type="text", nullable=true)
     */
    protected $usaf;

    /**
     * @var string
     *
     * @ORM\Column(name="wban", type="text", nullable=true)
     */
    protected $wban;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="text", nullable=true)
     */
    protected $country;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="text", nullable=true)
     */
    protected $state;

    /**
     * @var string
     *
     * @ORM\Column(name="icao", type="text", nullable=true)
     */
    protected $icao;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float", nullable=true)
     */
    protected $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float", nullable=true)
     */
    protected $longitude;

    /**
     * @var float
     *
     * @ORM\Column(name="elevation", type="float", nullable=true)
     */
    protected $elevation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="begin_time", type="utcdatetime", nullable=true)
     */
    protected $beginTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_time", type="utcdatetime", nullable=true)
     */
    protected $endTime;

    /**
     * Factory.
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Station
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Factory.
     *
     * @param  array  $parameters
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Station
     */
    public static function createFromParameters(array $parameters)
    {
        $station = static::create();
        $station->setParameters($parameters);

        return $station;
    }

    /**
     * Sets the values of this class given an array of parameters from the
     * NOAA history file.
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $parameter => $value) {
            $this->setParameter($parameter, $value);
        }

        return $this;
    }

    /**
     * Set a value in thie class based upon the parameter from the NOAA
     * history file.
     *
     * @param string $parameter
     * @param mixed  $value
     *
     * @return  self
     *
     * @throws  \InvalidArgumentException If the parameter is unknown.
     */
    public function setParameter($parameter, $value)
    {
        if (!$value) {
            $value = null;
        }

        switch ($parameter) {
            case 'USAF':
                return $this->setUsaf($value);
            case 'WBAN':
                return $this->setWban($value);
            case 'STATION NAME':
                return $this->setName($value);
            case 'CTRY':
                return $this->setCountry($value);
            case 'STATE':
                return $this->setState($value);
            case 'ICAO':
                return $this->setIcao($value);
            case 'LAT':
                return $this->setLatitude($this->parseCartesianCoordinateItem($value));
            case 'LON':
                return $this->setLongitude($this->parseCartesianCoordinateItem($value));
            case 'ELEV(M)':
                return $this->setElevation($this->parseElevation($value));
            case 'BEGIN':
                return $this->setBeginTime($this->parseDateField($value));
            case 'END':
                return $this->setEndTime($this->parseDateField($value));
        }

        throw new \InvalidArgumentException('Unknown parameter: ' . $parameter);
    }

    /**
     * Parse the format of latitude or longitude from a NOAA history file.
     *
     * @param  string $item Example: +46817|-089806
     *
     * @return float
     */
    protected function parseCartesianCoordinateItem($item)
    {
        if (null === $item = $this->parseFloatField($item)) {
            return null;
        }

        return $item;
    }

    /**
     * Parse the format of elevation from a NOAA history file.
     *
     * @param  string $elevation
     *
     * @return float
     */
    protected function parseElevation($elevation)
    {
        if (null === $elevation = $this->parseFloatField($elevation)) {
            return null;
        }

        return $elevation;
    }

    /**
     * Will return null if the NOAA history file numeric data field is deemed
     * to contain no data. Otherwise, return the float value of the original
     * field.
     *
     * @param  string $value
     *
     * @return float|null
     */
    protected function parseFloatField($value)
    {
        if (null === $value) {
            return null;
        }

        if (false === $value = filter_var($value, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Could not validate as a float: ' . $value);
        }

        return $value;
    }

    /**
     * Will return a DateTime object or null as it tries to parse a date field
     * from a NOAA history file.
     *
     * @param  string $value
     *
     * @return \DateTime|null
     */
    protected function parseDateField($value)
    {
        if (null === $value) {
            return null;
        }

        return new \DateTime($value);
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
     * Gets the value of name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param string $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Sets the value of country.
     *
     * @param string $country the country
     *
     * @return self
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Gets the value of state.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sets the value of state.
     *
     * @param string $state the state
     *
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Gets the value of icao.
     *
     * @return string
     */
    public function getIcao()
    {
        return $this->icao;
    }

    /**
     * Sets the value of icao.
     *
     * @param string $icao the icao
     *
     * @return self
     */
    public function setIcao($icao)
    {
        $this->icao = $icao;

        return $this;
    }

    /**
     * Set the value of latitude and longitude.
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
     * Gets the value of latitude.
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Sets the value of latitude.
     *
     * @param float $latitude the latitude
     *
     * @return self
     */
    public function setLatitude($latitude)
    {
        if ($latitude !== null && false === $latitude = filter_var($latitude, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate latitude as a float: ' . $latitude);
        }

        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Gets the value of longitude.
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Sets the value of longitude.
     *
     * @param float $longitude the longitude
     *
     * @return self
     */
    public function setLongitude($longitude)
    {
        if ($longitude !== null && false === $longitude = filter_var($longitude, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate longitude as a float: ' . $longitude);
        }

        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Gets the value of elevation.
     *
     * @return float
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * Sets the value of elevation.
     *
     * @param float $elevation the elevation
     *
     * @return self
     */
    public function setElevation($elevation)
    {
        $this->elevation = $elevation;

        return $this;
    }

    /**
     * Gets the value of beginTime.
     *
     * @return \DateTime
     */
    public function getBeginTime()
    {
        return $this->beginTime;
    }

    /**
     * Sets the value of beginTime.
     *
     * @param \DateTime $beginTime the begin time
     *
     * @return self
     */
    public function setBeginTime(\DateTime $beginTime = null)
    {
        $this->beginTime = $beginTime;

        return $this;
    }

    /**
     * Gets the value of endTime.
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Sets the value of endTime.
     *
     * @param \DateTime $endTime the end time
     *
     * @return self
     */
    public function setEndTime(\DateTime $endTime = null)
    {
        $this->endTime = $endTime;

        return $this;
    }
}
