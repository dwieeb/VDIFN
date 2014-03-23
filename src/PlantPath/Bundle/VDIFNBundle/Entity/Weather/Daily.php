<?php

namespace PlantPath\Bundle\VDIFNBundle\Entity\Weather;

use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use PlantPath\Bundle\VDIFNBundle\Geo\Temperature;
use Doctrine\ORM\Mapping as ORM;

/**
 * Daily weather data.
 *
 * @ORM\Entity(repositoryClass="PlantPath\Bundle\VDIFNBundle\Repository\Weather\DailyRepository")
 * @ORM\Table(
 *     name="weather_daily",
 *     indexes={
 *         @ORM\Index(name="time_location_dsv_idx", columns={"time", "latitude", "longitude", "dsv"})
 *     }
 * )
 */
class Daily
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
     * @ORM\Column(name="time", type="utcdatetime")
     */
    protected $time;

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
     * @var integer
     *
     * @ORM\Column(name="mean_temperature", type="float")
     */
    protected $meanTemperature;

    /**
     * @var integer
     *
     * @ORM\Column(name="leaf_wetting_time", type="smallint")
     */
    protected $leafWettingTime;

    /**
     * @var array
     */
    protected static $dsvMatrix;

    /**
     * Factory.
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Weather\Daily
     */
    public static function create()
    {
        return new static();
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
     * Builds the DSV Matrix if not already built and returns it.
     *
     * @return array
     */
    public static function getDsvMatrix()
    {
        if (null === self::$dsvMatrix) {
            self::$dsvMatrix = [];

            $a = array_merge(
                array_fill_keys(range(0, 6), 0),
                array_fill_keys(range(7, 15), 1),
                array_fill_keys(range(16, 20), 2),
                array_fill_keys(range(21, 24), 3)
            );

            foreach (range(13, 17) as $i) {
                self::$dsvMatrix[$i] =& $a;
            }

            $b = array_merge(
                array_fill_keys(range(0, 3), 0),
                array_fill_keys(range(4, 8), 1),
                array_fill_keys(range(9, 15), 2),
                array_fill_keys(range(16, 22), 3),
                array_fill_keys(range(23, 24), 4)
            );

            foreach (range(18, 20) as $i) {
                self::$dsvMatrix[$i] =& $b;
            }

            $c = array_merge(
                array_fill_keys(range(0, 2), 0),
                array_fill_keys(range(3, 5), 1),
                array_fill_keys(range(6, 12), 2),
                array_fill_keys(range(13, 20), 3),
                array_fill_keys(range(21, 24), 4)
            );

            foreach (range(21, 25) as $i) {
                self::$dsvMatrix[$i] =& $c;
            }

            $d = array_merge(
                array_fill_keys(range(0, 3), 0),
                array_fill_keys(range(4, 8), 1),
                array_fill_keys(range(9, 15), 2),
                array_fill_keys(range(16, 22), 3),
                array_fill_keys(range(23, 24), 4)
            );

            foreach (range(26, 29) as $i) {
                self::$dsvMatrix[$i] =& $d;
            }
        }

        return self::$dsvMatrix;
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
     * @param \DateTime $time
     *
     * @return self
     */
    public function setTime(\DateTime $time)
    {
        $this->time = $time;

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
     * @param float $latitude
     *
     * @return self
     */
    public function setLatitude($latitude)
    {
        if (false === $latitude = filter_var($latitude, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate latitude as a float');
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
     * @param float $longitude
     *
     * @return self
     */
    public function setLongitude($longitude)
    {
        if (false === $longitude = filter_var($longitude, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate longitude as a float');
        }

        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Gets the value of dsv.
     *
     * @return integer
     */
    public function getDsv()
    {
        return $this->dsv;
    }

    /**
     * Sets the value of dsv.
     *
     * @param integer $dsv
     *
     * @return self
     */
    public function setDsv($dsv)
    {
        if (false === $dsv = filter_var($dsv, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Cannot validate disease severity value as an integer');
        }

        if ($dsv < 0 || $dsv > 4) {
            throw new \RangeException('Disease severity value must be between 0 and 4');
        }

        $this->dsv = $dsv;

        return $this;
    }

    /**
     * Given existing data, compute the disease severity value for this object.
     */
    public function computeDsv()
    {
        if (null === $this->getMeanTemperature() || null === $this->getLeafWettingTime()) {
            throw new \UnexpectedValueException('Both mean temperature and leaf-wetting time must be defined');
        }

        $matrix = self::getDsvMatrix();
        $meanTemperature = (int) $this->getMeanTemperature();
        $leafWettingTime = $this->getLeafWettingTime();

        if (
            array_key_exists($meanTemperature, $matrix) &&
            array_key_exists($leafWettingTime, $matrix[$meanTemperature])
        ) {
            $dsv = $matrix[$meanTemperature][$leafWettingTime];
        } else {
            $dsv = 0;
        }

        $this->setDsv($dsv);

        return $this;
    }

    /**
     * Gets the value of meanTemperature.
     *
     * @return float
     */
    public function getMeanTemperature()
    {
        return $this->meanTemperature;
    }

    /**
     * Sets the value of meanTemperature.
     *
     * @param float $meanTemperature
     *
     * @return self
     */
    public function setMeanTemperature($meanTemperature)
    {
        if (false === $meanTemperature = filter_var($meanTemperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate mean temperature as a float');
        }

        $this->meanTemperature = $meanTemperature;

        return $this;
    }

    /**
     * Gets the value of leafWettingTime.
     *
     * @return integer
     */
    public function getLeafWettingTime()
    {
        return $this->leafWettingTime;
    }

    /**
     * Sets the value of leafWettingTime.
     *
     * @param integer $leafWettingTime
     *
     * @return self
     */
    public function setLeafWettingTime($leafWettingTime)
    {
        if (false === $leafWettingTime = filter_var($leafWettingTime, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Cannot validate leaf-wetting time as an integer');
        }

        $this->leafWettingTime = $leafWettingTime;

        return $this;
    }
}
