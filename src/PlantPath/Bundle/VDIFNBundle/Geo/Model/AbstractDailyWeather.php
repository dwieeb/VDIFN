<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

use PlantPath\Bundle\VDIFNBundle\Geo\Crop;
use PlantPath\Bundle\VDIFNBundle\Geo\Disease;
use PlantPath\Bundle\VDIFNBundle\Geo\Model\CarrotFoliarDiseaseModel;
use PlantPath\Bundle\VDIFNBundle\Geo\Model\LateBlightDiseaseModel;
use PlantPath\Bundle\VDIFNBundle\Geo\Model\DiseaseModel;

abstract class AbstractDailyWeather implements DailyWeatherInterface
{
    /**
     * @var integer
     */
    protected $meanTemperature;

    /**
     * @var integer
     */
    protected $leafWettingTime;

    /**
     * @var integer
     */
    protected $dsvCarrotFoliarDisease;

    /**
     * @var string
     */
    protected $crop;

    /**
     * @var string
     */
    protected $infliction;

    /**
     * @var integer
     */
    protected $dsvPotatoLateBlight;

    /**
     * Given existing data, compute the disease severity value for this object.
     *
     * @return this
     */
    public function calculateDsv()
    {
        $meanTemperature = $this->getMeanTemperature();
        $leafWettingTime = $this->getLeafWettingTime();

        $this->setDsvCarrotFoliarDisease(CarrotFoliarDiseaseModel::apply($meanTemperature, $leafWettingTime));
        $this->setDsvPotatoLateBlight(LateBlightDiseaseModel::apply($meanTemperature, $leafWettingTime));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMeanTemperature()
    {
        return $this->meanTemperature;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function getLeafWettingTime()
    {
        return $this->leafWettingTime;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function setCurrentModel($crop, $infliction)
    {
        $this->crop = $crop;
        $this->infliction = $infliction;
    }

    /**
     * {@inheritDoc}
     */
    public function getDsv()
    {
        switch ($this->crop) {
        case Crop::CARROT:
            switch ($this->infliction) {
            case Disease::FOLIAR_DISEASE:
                return $this->getDsvCarrotFoliarDisease();
            }

            break;
        case Crop::POTATO:
            switch ($this->infliction) {
            case Disease::LATE_BLIGHT:
                return $this->getDsvPotatoLateBlight();
            }
        }

        throw new \InvalidArgumentException("Unknown DSV by current model with $crop and $infliction.");
    }

    /**
     * {@inheritDoc}
     */
    public function getDsvCarrotFoliarDisease()
    {
        return $this->dsvCarrotFoliarDisease;
    }

    /**
     * {@inheritDoc}
     */
    public function setDsvCarrotFoliarDisease($dsvCarrotFoliarDisease)
    {
        $dsvCarrotFoliarDisease = DiseaseModel::validateDsv($dsvCarrotFoliarDisease);

        $this->dsvCarrotFoliarDisease = $dsvCarrotFoliarDisease;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDsvPotatoLateBlight()
    {
        return $this->dsvPotatoLateBlight;
    }

    /**
     * {@inheritDoc}
     */
    public function setDsvPotatoLateBlight($dsvPotatoLateBlight)
    {
        $dsvPotatoLateBlight = DiseaseModel::validateDsv($dsvPotatoLateBlight);

        $this->dsvPotatoLateBlight = $dsvPotatoLateBlight;

        return $this;
    }
}
