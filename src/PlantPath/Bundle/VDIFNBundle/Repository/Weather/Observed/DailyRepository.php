<?php

namespace PlantPath\Bundle\VDIFNBundle\Repository\Weather\Observed;

use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use Doctrine\ORM\EntityRepository;

class DailyRepository extends EntityRepository
{
    /**
     * Find daily weather data given a datetime and location.
     *
     * @param  string   $usaf
     * @param  string   $wban
     * @param  DateTime $dt
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Weather\Observed\Daily
     */
    public function getOneByStationAndTime($usaf, $wban, \DateTime $dt)
    {
        return $this->findOneBy([
            'usaf' => $usaf,
            'wban' => $wban,
            'time' => $dt,
        ]);
    }
}
