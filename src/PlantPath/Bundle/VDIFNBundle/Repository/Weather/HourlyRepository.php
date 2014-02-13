<?php

namespace PlantPath\Bundle\VDIFNBundle\Repository\Weather;

use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use Doctrine\ORM\EntityRepository;

class HourlyRepository extends EntityRepository
{
    /**
     * Find hourly weather data given a datetime and location.
     *
     * @param  DateTime $dt
     * @param  Point    $point
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Weather\Hourly
     */
    public function getOneBySpaceTime(\DateTime $dt, Point $point)
    {
        return $this->findOneBy([
            'verificationTime' => $dt,
            'latitude' => $point->getLatitude(),
            'longitude' => $point->getLongitude(),
        ]);
    }
}
