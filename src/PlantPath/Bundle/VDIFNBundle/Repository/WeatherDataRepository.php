<?php

namespace PlantPath\Bundle\VDIFNBundle\Repository;

use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use Doctrine\ORM\EntityRepository;

class WeatherDataRepository extends EntityRepository
{
    /**
     * Find weather data given a date and location.
     *
     * @param  DateTime $date
     * @param  Point    $point
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\WeatherDate
     */
    public function getOneBySpaceTime(\DateTime $date, Point $point)
    {
        return $this->findOneBy([
            'referenceTime' => $date,
            'latitude' => $point->getLatitude(),
            'longitude' => $point->getLongitude(),
        ]);
    }
}
