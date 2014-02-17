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
     * Find daily weather data given a datetime and bounding box.
     *
     * @param  DateTime $dt
     * @param  Point    $nw
     * @param  Point    $se
     * @param  array    $projection
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Weather\Daily
     */
    public function getWithinBoundingBox(\DateTime $dt, Point $nw, Point $se, array $projection = ['d.latitude', 'd.longitude', 'd.dsv'])
    {
        return $this
            ->createQueryBuilder('d')
            ->select($projection)
            ->where('d.latitude BETWEEN :s AND :n')
            ->andWhere('d.longitude BETWEEN :w AND :e')
            ->andWhere('d.time = :dt')
            ->setParameter('n', $nw->getLatitude())
            ->setParameter('e', $se->getLongitude())
            ->setParameter('s', $se->getLatitude())
            ->setParameter('w', $nw->getLongitude())
            ->setParameter('dt', $dt)
            ->getQuery()
            ->getResult();
    }
}
