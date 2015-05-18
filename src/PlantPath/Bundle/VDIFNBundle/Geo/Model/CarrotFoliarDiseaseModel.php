<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

class CarrotFoliarDiseaseModel extends DiseaseModel
{
    /**
     * @var array
     */
    protected static $matrix;

    /**
     * {@inheritDoc}
     */
    protected static function getMatrix()
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

    public static function apply($meanTemperature, $leafWettingTime)
    {
        $matrix = self::getDsvMatrix();

        if (false === $meanTemperature = filter_var($meanTemperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate mean temperature as a float');
        }

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
}
