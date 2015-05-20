<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

use PlantPath\Bundle\VDIFNBundle\Geo\Threshold;

class LateBlightDiseaseModel extends DiseaseModel
{
    /**
     * {@inheritDoc}
     */
    public static function apply($meanTemperature, $leafWettingTime)
    {
        if (false === $meanTemperature = filter_var($meanTemperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate mean temperature as a float');
        }

        if (false === $leafWettingTime = filter_var($leafWettingTime, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Cannot validate leaf-wetting time as an integer');
        }

        if ($meanTemperature >= 7.2222 && $meanTemperature < 12.2222) {
            if ($leafWettingTime >= 0 && $leafWettingTime <= 15) {
                return 0;
            } else if ($leafWettingTime >= 16 && $leafWettingTime <= 18) {
                return 1;
            } else if ($leafWettingTime >= 19 && $leafWettingTime <= 21) {
                return 2;
            } else if ($leafWettingTime >= 22 && $leafWettingTime <= 24) {
                return 3;
            }
        } else if ($meanTemperature >= 12.2222 && $meanTemperature < 15.5555) {
            if ($leafWettingTime >= 0 && $leafWettingTime <= 12) {
                return 0;
            } else if ($leafWettingTime >= 13 && $leafWettingTime <= 15) {
                return 1;
            } else if ($leafWettingTime >= 16 && $leafWettingTime <= 18) {
                return 2;
            } else if ($leafWettingTime >= 19 && $leafWettingTime <= 21) {
                return 3;
            } else if ($leafWettingTime >= 22 && $leafWettingTime <= 24) {
                return 4;
            }
        } else if ($meanTemperature >= 15.5555 && $meanTemperature < 26.6667) {
            if ($leafWettingTime >= 0 && $leafWettingTime <= 9) {
                return 0;
            } else if ($leafWettingTime >= 10 && $leafWettingTime <= 12) {
                return 1;
            } else if ($leafWettingTime >= 13 && $leafWettingTime <= 15) {
                return 2;
            } else if ($leafWettingTime >= 16 && $leafWettingTime <= 18) {
                return 3;
            } else if ($leafWettingTime >= 19 && $leafWettingTime <= 24) {
                return 4;
            }
        }

        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public static function determineThreshold(DiseaseModelData $data)
    {
        // TODO
    }
}
