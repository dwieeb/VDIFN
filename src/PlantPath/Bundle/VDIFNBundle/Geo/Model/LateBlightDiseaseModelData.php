<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

class LateBlightDiseaseModelData extends DiseaseModelData
{
    /**
     * The status of late blight for this season.
     *
     * @var string
     */
    protected $lateBlightStatus;

    /**
     * Get lateBlightStatus.
     *
     * @return lateBlightStatus.
     */
    public function getLateBlightStatus()
    {
        return $this->lateBlightStatus;
    }

    /**
     * Set lateBlightStatus.
     *
     * @param lateBlightStatus the value to set.
     */
    public function setLateBlightStatus($lateBlightStatus)
    {
        if (!LateBlightStatus::isValidSlug($lateBlightStatus)) {
            throw new \InvalidArgumentException("$lateBlightStatus is not a valid late blight status slug.");
        }

        $this->lateBlightStatus = $lateBlightStatus;

        return $this;
    }
}
