<?php

namespace PlantPath\Bundle\VDIFNBundle\Entity\Weather\Observed;

use PlantPath\Bundle\VDIFNBundle\Geo\Model\CarrotFoliarDiseaseModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * Observed daily weather data.
 *
 * @ORM\Entity(repositoryClass="PlantPath\Bundle\VDIFNBundle\Repository\Weather\Observed\DailyRepository")
 * @ORM\Table(
 *     name="weather_observed_daily",
 *     indexes={
 *         @ORM\Index(name="daily_usaf_wban_time_idx", columns={"usaf", "wban", "time"})
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
     * Factory.
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Weather\Observed\Daily
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Given existing data, compute the disease severity value for this object.
     */
    public function calculateDsv()
    {
        $dsv = CarrotFoliarDiseaseModel::apply(
            $this->getMeanTemperature(),
            $this->getLeafWettingTime()
        );

        $this->setDsv($dsv);

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
     * @param integer $dsv the dsv
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
     * Gets the value of meanTemperature.
     *
     * @return integer
     */
    public function getMeanTemperature()
    {
        return $this->meanTemperature;
    }

    /**
     * Sets the value of meanTemperature.
     *
     * @param integer $meanTemperature the mean temperature
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
     * @param integer $leafWettingTime the leaf wetting time
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
