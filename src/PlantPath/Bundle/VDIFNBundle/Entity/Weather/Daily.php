<?php

namespace PlantPath\Bundle\VDIFNBundle\Entity\Weather;

use PlantPath\Bundle\VDIFNBundle\Geo\DiseaseSeverity;
use PlantPath\Bundle\VDIFNBundle\Geo\DegreeDay;
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
     * @var float
     *
     * @ORM\Column(name="dd10", type="float")
     */
    protected $degreeDay10;

    /**
     * @var float
     *
     * @ORM\Column(name="dd7_2", type="float")
     */
    protected $degreeDay72;

    /**
     * @var float
     *
     * @ORM\Column(name="dd4_4", type="float")
     */
    protected $degreeDay44;

    /**
     * @var float
     *
     * @ORM\Column(name="dd2_7", type="float")
     */
    protected $degreeDay27;

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
     *
     * @return this
     */
    public function calculateDsv()
    {
        $ds = DiseaseSeverity::create(
            $this->getMeanTemperature(),
            $this->getLeafWettingTime()
        );

        $this->setDsv($ds->calculate());

        return $this;
    }

    /**
     * Given existing data, calculate the degree days for this object.
     *
     * @return this
     */
    public function calculateDegreeDays()
    {
        $meanTemperature = $this->getMeanTemperature();
        $this->setDegreeDay10(DegreeDay::create(10.0, $meanTemperature)->calculate());
        $this->setDegreeDay72(DegreeDay::create(7.2222, $meanTemperature)->calculate());
        $this->setDegreeDay44(DegreeDay::create(4.44444, $meanTemperature)->calculate());
        $this->setDegreeDay27(DegreeDay::create(2.7778, $meanTemperature)->calculate());

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

    /**
     * Gets the value of degreeDay10.
     *
     * @return float
     */
    public function getDegreeDay10()
    {
        return $this->degreeDay10;
    }

    /**
     * Sets the value of degreeDay10.
     *
     * @param float $degreeDay10 the degree day10
     *
     * @return self
     */
    public function setDegreeDay10($degreeDay10)
    {
        if (false === $degreeDay10 = filter_var($degreeDay10, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate degree day as a float');
        }

        $this->degreeDay10 = $degreeDay10;

        return $this;
    }

    /**
     * Gets the value of degreeDay72.
     *
     * @return float
     */
    public function getDegreeDay72()
    {
        return $this->degreeDay72;
    }

    /**
     * Sets the value of degreeDay72.
     *
     * @param float $degreeDay72 the degree day72
     *
     * @return self
     */
    public function setDegreeDay72($degreeDay72)
    {
        if (false === $degreeDay72 = filter_var($degreeDay72, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate degree day as a float');
        }

        $this->degreeDay72 = $degreeDay72;

        return $this;
    }

    /**
     * Gets the value of degreeDay44.
     *
     * @return float
     */
    public function getDegreeDay44()
    {
        return $this->degreeDay44;
    }

    /**
     * Sets the value of degreeDay44.
     *
     * @param float $degreeDay44 the degree day44
     *
     * @return self
     */
    public function setDegreeDay44($degreeDay44)
    {
        if (false === $degreeDay44 = filter_var($degreeDay44, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate degree day as a float');
        }

        $this->degreeDay44 = $degreeDay44;

        return $this;
    }

    /**
     * Gets the value of degreeDay27.
     *
     * @return float
     */
    public function getDegreeDay27()
    {
        return $this->degreeDay27;
    }

    /**
     * Sets the value of degreeDay27.
     *
     * @param float $degreeDay27 the degree day27
     *
     * @return self
     */
    public function setDegreeDay27($degreeDay27)
    {
        if (false === $degreeDay27 = filter_var($degreeDay27, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate degree day as a float');
        }

        $this->degreeDay27 = $degreeDay27;

        return $this;
    }
}
