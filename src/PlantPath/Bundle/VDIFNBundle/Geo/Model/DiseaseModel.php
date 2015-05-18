<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

abstract class DiseaseModel extends AbstractModel implements DiseaseModelInterface
{
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
