<?php

namespace PlantPath\Bundle\VDIFNBundle\Repository;

use Doctrine\ORM\EntityRepository;

class StationRepository extends EntityRepository
{
    /**
     * Find the open stations in a state. Optionally, pass in a date to which
     * to compare the end date of stations.
     *
     * @param  string   $country
     * @param  string   $state
     *
     * @return PlantPath\Bundle\VDIFNBundle\Entity\Station
     */
    public function getOpenByCountryAndState($country, $state)
    {
        return $this
            ->createQueryBuilder('s')
            ->select('s.usaf, s.wban, s.name, s.latitude, s.longitude, s.elevation')
            ->where('s.country = :country')
            ->andWhere('s.state = :state')
            // ->andWhere('s.beginTime IS NOT NULL')
            ->andWhere('s.endTime IS NULL')
            ->setParameter('country', $country)
            ->setParameter('state', $state)
            ->getQuery()
            ->getResult();
    }
}
