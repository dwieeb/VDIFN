<?php

namespace PlantPath\Bundle\VDIFNBundle\Repository\Weather;

use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use Doctrine\ORM\EntityRepository;

class DailyRepository extends EntityRepository
{
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

    /**
     * Find daily weather data based upon a date range.
     *
     * @param  DateTime $start
     * @param  DateTime $end
     */
    public function getDsvSumsWithinDateRange(\DateTime $start, \DateTime $end)
    {
        return $this
            ->createQueryBuilder('d')
            ->select('d.latitude, d.longitude, SUM(d.dsv) AS dsv')
            ->where('d.time BETWEEN :start AND :end')
            ->groupBy('d.latitude, d.longitude')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }
}
