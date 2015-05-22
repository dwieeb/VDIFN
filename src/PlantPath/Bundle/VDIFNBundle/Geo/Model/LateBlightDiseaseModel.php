<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

use PlantPath\Bundle\VDIFNBundle\Geo\Threshold;

class LateBlightDiseaseModel extends DiseaseModel
{
    /**
     * @var array
     */
    protected static $thresholds;

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
        if (!($data instanceof LateBlightDiseaseModelData)) {
            throw new \InvalidArgumentException("Incompatible model data.");
        }

        $dayTotal = $data->getDayTotal();
        $seasonTotal = $data->getSeasonTotal();
        $status = $data->getLateBlightStatus();

        if ($status === LateBlightStatus::WIDESPREAD_OUTBREAK) {
            return Threshold::HIGH;
        }

        if ($dayTotal >= 21 || $status === LateBlightStatus::ISOLATED_OUTBREAK) {
            return Threshold::HIGH;
        }

        if ($status === LateBlightStatus::NOT_OBSERVED) {
            if ($dayTotal >= 3 || $seasonTotal > 30) {
                return Threshold::MEDIUM;
            }

            if ($dayTotal <= 3 && $seasonTotal < 30) {
                return Threshold::LOW;
            }
        }

        throw new \InvalidArgumentException("Unable to determine threshold with given data.");
    }

    /**
     * {@inheritDoc}
     */
    public static function getThresholds()
    {
        if (null === static::$thresholds) {
            static::$thresholds = [
                Threshold::HIGH => [
                    'name' => Threshold::getNameBySlug(Threshold::HIGH),
                    'description' => 'High likelihood of disease<br />(widespread outbreak observed OR 7-day accumulated DSVs &ge; 21 or isolated outbreak observed)',
                    'color' => '#cc0000',
                ],
                Threshold::MEDIUM => [
                    'name' => Threshold::getNameBySlug(Threshold::MEDIUM),
                    'description' => 'Medium likelihood of disease<br />(7-day accumulated DSVs &ge; 3 or season accumulated DSVs &gt; 30)',
                    'color' => '#ffd700',
                ],
                Threshold::LOW => [
                    'name' => Threshold::getNameBySlug(Threshold::LOW),
                    'description' => 'Low likelihood of disease<br />(7-day accumulated DSVs &le; 3 and season accumulated DSVs &lt; 30)',
                    'color' => '#00c957',
                ],
            ];
        }

        return static::$thresholds;
    }
}
