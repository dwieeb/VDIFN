<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

use PlantPath\Bundle\VDIFNBundle\Geo\Crop;
use PlantPath\Bundle\VDIFNBundle\Geo\Disease;
use PlantPath\Bundle\VDIFNBundle\Geo\DsvCalculableInterface;

abstract class DiseaseModel extends AbstractModel implements DiseaseModelInterface
{
    /**
     * @param string crop
     * @param string disease
     *
     * @return string
     */
    public static function getClassByCropAndDisease($crop, $disease)
    {
        switch ($crop) {
        case Crop::CARROT:
            switch ($disease) {
            case Disease::FOLIAR_DISEASE:
                return 'PlantPath\Bundle\VDIFNBundle\Geo\Model\CarrotFoliarDiseaseModel';
            }

            break;
        case Crop::POTATO:
            switch ($disease) {
            case Disease::LATE_BLIGHT:
                return 'PlantPath\Bundle\VDIFNBundle\Geo\Model\LateBlightDiseaseModel';
            }

            break;
        }

        throw new \InvalidArgumentException("Cound not determine disease model class by $crop and $disease.");
    }

    /**
     * Validate the value of a supposed DSV and return the filtered version.
     *
     * @param mixed $dsv
     *
     * @return int
     */
    public static function validateDsv($dsv)
    {
        if (false === $dsv = filter_var($dsv, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Cannot validate disease severity value as an integer');
        }

        if ($dsv < 0 || $dsv > 4) {
            throw new \RangeException('Disease severity value must be between 0 and 4');
        }

        return $dsv;
    }

    /**
     * Given an array of objects that allow calculation of DSVs, calculate the
     * mean temperature and leaf-wetting time of that group of hourlies.
     *
     * @param array $hourlies
     * @param int $threshold
     *
     * @return array
     */
    public static function calculateTemperatureAndLeafWettingTime(array $hourlies, $threshold)
    {
        $leafWettingTime = 0;
        $sumTemperature = 0;

        foreach ($hourlies as $hourly) {
            if (!($hourly instanceof DsvCalculableInterface)) {
                throw new \RuntimeException('Hourly is missing implementation detail.');
            }

            if ($hourly->getRelativeHumidity() > $threshold) {
                $leafWettingTime += 1;
            }

            $sumTemperature += $hourly->getTemperature();
        }

        $meanTemperature = $sumTemperature / count($hourlies);

        return [$meanTemperature, $leafWettingTime];
    }
}
