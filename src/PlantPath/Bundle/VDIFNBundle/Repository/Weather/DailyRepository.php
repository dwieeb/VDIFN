<?php

namespace PlantPath\Bundle\VDIFNBundle\Repository\Weather;

use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use Doctrine\ORM\EntityRepository;

class DailyRepository extends EntityRepository
{
    /**
     * Find the latest weather data entry.
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Weather\Daily
     */
    public function getLatestOne()
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.time', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleResult();
    }

    /**
     * Find daily weather data given a datetime and location.
     *
     * @param  DateTime $dt
     * @param  Point    $point
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Weather\Daily
     */
    public function getOneBySpaceTime(\DateTime $dt, Point $point)
    {
        return $this->findOneBy([
            'time' => $dt,
            'latitude' => $point->getLatitude(),
            'longitude' => $point->getLongitude(),
        ]);
    }
}
