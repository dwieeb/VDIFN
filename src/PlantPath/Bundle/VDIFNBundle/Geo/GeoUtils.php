<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class GeoUtils
{
    /**
     * Return latitudinal distance.
     *
     * @param  int|float $km
     *
     * @return float
     */
    public static function kmToLatitude($km)
    {
        return 1 / (110.54 / $km);
    }

    /**
     * Return longitudinal distance.
     *
     * @param  int|float $km
     * @param  int|float $latitude
     *
     * @return float
     */
    public static function kmToLongitude($km, $latitude)
    {
        return 1 / ((111.32 * cos($latitude * pi() / 180)) / $km);
    }
}
