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
     * @param  array   $projection
     */
    public function getWithinDateRange(\DateTime $start, \DateTime $end, array $projection = [])
    {
        $qb = $this->createQueryBuilder('d');

        if (!empty($projection)) {
            $qb->select($projection);
        }

        return $qb
            ->where('d.time BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->iterate();
    }
}
