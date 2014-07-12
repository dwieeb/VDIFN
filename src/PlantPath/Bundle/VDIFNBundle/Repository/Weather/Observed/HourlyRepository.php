<?php

namespace PlantPath\Bundle\VDIFNBundle\Repository\Weather\Observed;

use Doctrine\ORM\EntityRepository;

class HourlyRepository extends EntityRepository
{
    /**
     * Find observed hourly weather data given a station and time.
     *
     * @param  string   $usaf
     * @param  string   $wban
     * @param  DateTime $dt
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Weather\Observed\Hourly
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
