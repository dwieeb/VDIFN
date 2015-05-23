<?php

namespace PlantPath\Bundle\VDIFNBundle\Entity\Weather\Observed;

use PlantPath\Bundle\VDIFNBundle\Geo\Model\AbstractDailyWeather;
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
class Daily extends AbstractDailyWeather
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
     * @ORM\Column(name="dsv_carrot_foliar_disease", type="smallint")
     */
    protected $dsvCarrotFoliarDisease;

    /**
     * @var integer
     *
     * @ORM\Column(name="dsv_potato_late_blight", type="smallint")
     */
    protected $dsvPotatoLateBlight;

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
}
