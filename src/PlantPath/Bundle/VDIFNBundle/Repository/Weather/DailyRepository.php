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
    public function getDsvAverageWithinDateRange(\DateTime $start, \DateTime $end)
    {
        $entities = $this
            ->createQueryBuilder('d')
            ->select('d.latitude, d.longitude, SUM(d.dsv) AS dsv')
            ->where('d.time BETWEEN :start AND :end')
            ->groupBy('d.latitude, d.longitude')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        $days = (abs($end->getTimestamp() - $start->getTimestamp()) / 60 / 60 / 24) + 1;

        return array_map(function($entity) use ($days) {
            $entity['dsv'] = round($entity['dsv'] / $days);
            return $entity;
        }, $entities);
    }
}
