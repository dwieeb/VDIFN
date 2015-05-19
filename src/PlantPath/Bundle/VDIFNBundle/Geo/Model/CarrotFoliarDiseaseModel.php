<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

use PlantPath\Bundle\VDIFNBundle\Geo\Threshold;

class CarrotFoliarDiseaseModel extends DiseaseModel
{
    /**
     * @var array
     */
    protected static $matrix;

    /**
     * {@inheritDoc}
     */
    public static function getMatrix()
    {
        if (null === self::$matrix) {
            self::$matrix = [];

            $a = array_merge(
                array_fill_keys(range(0, 6), 0),
                array_fill_keys(range(7, 15), 1),
                array_fill_keys(range(16, 20), 2),
                array_fill_keys(range(21, 24), 3)
            );

            foreach (range(13, 17) as $i) {
                self::$matrix[$i] =& $a;
            }

            $b = array_merge(
                array_fill_keys(range(0, 3), 0),
                array_fill_keys(range(4, 8), 1),
                array_fill_keys(range(9, 15), 2),
                array_fill_keys(range(16, 22), 3),
                array_fill_keys(range(23, 24), 4)
            );

            foreach (range(18, 20) as $i) {
                self::$matrix[$i] =& $b;
            }

            $c = array_merge(
                array_fill_keys(range(0, 2), 0),
                array_fill_keys(range(3, 5), 1),
                array_fill_keys(range(6, 12), 2),
                array_fill_keys(range(13, 20), 3),
                array_fill_keys(range(21, 24), 4)
            );

            foreach (range(21, 25) as $i) {
                self::$matrix[$i] =& $c;
            }

            $d = array_merge(
                array_fill_keys(range(0, 3), 0),
                array_fill_keys(range(4, 8), 1),
                array_fill_keys(range(9, 15), 2),
                array_fill_keys(range(16, 22), 3),
                array_fill_keys(range(23, 24), 4)
            );

            foreach (range(26, 29) as $i) {
                self::$matrix[$i] =& $d;
            }
        }

        return self::$matrix;
    }

    /**
     * {@inheritDoc}
     */
    public static function apply($meanTemperature, $leafWettingTime)
    {
        $matrix = self::getMatrix();

        if (false === $meanTemperature = filter_var($meanTemperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate mean temperature as a float');
        }

        $meanTemperature = (int) $meanTemperature;

        if (false === $leafWettingTime = filter_var($leafWettingTime, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Cannot validate leaf-wetting time as an integer');
        }

        if (
            array_key_exists($meanTemperature, $matrix) &&
            array_key_exists($leafWettingTime, $matrix[$meanTemperature])
        ) {
            $dsv = $matrix[$meanTemperature][$leafWettingTime];
        } else {
            $dsv = 0;
        }

        return $dsv;
    }

    /**
     * {@inheritDoc}
     */
    public static function determineThreshold(DiseaseModelData $data)
    {
        $dayTotal = $data->getDayTotal();

        if ($dayTotal >= 0 && $dayTotal < 5) {
            return Threshold::VERY_LOW;
        } else if ($dayTotal >= 5 && $dayTotal < 10) {
            return Threshold::LOW;
        } else if ($dayTotal >= 10 && $dayTotal < 15) {
            return Threshold::MEDIUM;
        } else if ($dayTotal >= 15 && $dayTotal < 20) {
            return Threshold::HIGH;
        } else if ($dayTotal >= 20) {
            return Threshold::VERY_HIGH;
        }

        throw new \InvalidArgumentException("Unable to determine threshold with given data.");
    }
}
