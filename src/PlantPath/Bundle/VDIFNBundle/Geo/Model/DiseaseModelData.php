<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

class DiseaseModelData
{
    /**
     * A running total of daily DSV values.
     *
     * @var int
     */
    protected $dayTotal;

    /**
     * Get dayTotal.
     *
     * @return dayTotal.
     */
    public function getDayTotal()
    {
        return $this->dayTotal;
    }

    /**
     * Set dayTotal.
     *
     * @param dayTotal the value to set.
     */
    public function setDayTotal($dayTotal)
    {
        if (false === $dayTotal = filter_var($dayTotal, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Cannot validate day total as an integer');
        }

        $this->dayTotal = $dayTotal;

        return $this;
    }
}
