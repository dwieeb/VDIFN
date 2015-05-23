<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

use Doctrine\Common\Persistence\ObjectManager;
use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use PlantPath\Bundle\VDIFNBundle\Geo\Crop;
use PlantPath\Bundle\VDIFNBundle\Geo\Disease;
use PlantPath\Bundle\VDIFNBundle\Geo\DsvCalculableInterface;

abstract class DiseaseModel extends AbstractModel implements DiseaseModelInterface
{
    /**
     * @var string
     */
    protected static $crop;

    /**
     * @var string
     */
    protected static $disease;

    /**
     * @param string crop
     * @param string disease
     *
     * @return string
     */
    public static function getClassByCropAndDisease($crop, $disease)
    {
        switch ($crop) {
        case Crop::CARROT:
            switch ($disease) {
            case Disease::FOLIAR_DISEASE:
                return 'PlantPath\Bundle\VDIFNBundle\Geo\Model\CarrotFoliarDiseaseModel';
            }

            break;
        case Crop::POTATO:
            switch ($disease) {
            case Disease::LATE_BLIGHT:
                return 'PlantPath\Bundle\VDIFNBundle\Geo\Model\LateBlightDiseaseModel';
            }

            break;
        }

        throw new \InvalidArgumentException("Cound not determine disease model class by $crop and $disease.");
    }

    /**
     * {@inheritDoc}
     */
    public static function getStationData(ObjectManager $em, $usaf, $wban, \DateTime $start, \DateTime $end)
    {
        if (null === static::$crop) {
            throw new \Exception('$crop must be set.');
        }

        if (null === static::$disease) {
            throw new \Exception('$disease must be set.');
        }

        $entities = $em
            ->getRepository('PlantPathVDIFNBundle:Weather\Observed\Daily')
            ->createQueryBuilder('d')
            ->where('d.usaf = :usaf')
            ->andWhere('d.wban = :wban')
            ->andWhere('d.time BETWEEN :start AND :end')
            ->orderBy('d.time', 'DESC')
            ->setParameter('usaf', $usaf)
            ->setParameter('wban', $wban)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        foreach ($entities as &$entity) {
            $entity->setCurrentModel(static::$crop, static::$disease);
        }

        return $entities;
    }

    /**
     * {@inheritDoc}
     */
    public static function getPointData(ObjectManager $em, Point $point, \DateTime $start, \DateTime $end)
    {
        if (null === static::$crop) {
            throw new \Exception('$crop must be set.');
        }

        if (null === static::$disease) {
            throw new \Exception('$disease must be set.');
        }

        $entities = $em
            ->getRepository('PlantPathVDIFNBundle:Weather\Daily')
            ->createQueryBuilder('d')
            ->where('d.time BETWEEN :start AND :end')
            ->andWhere('d.latitude = :latitude')
            ->andWhere('d.longitude = :longitude')
            ->orderBy('d.time', 'DESC')
            ->setParameter('latitude', $point->getLatitude())
            ->setParameter('longitude', $point->getLongitude())
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        foreach ($entities as &$entity) {
            $entity->setCurrentModel(static::$crop, static::$disease);
        }

        return $entities;
    }

    /**
     * Validate the value of a supposed DSV and return the filtered version.
     *
     * @param mixed $dsv
     *
     * @return int
     */
    public static function validateDsv($dsv)
    {
        if (false === $dsv = filter_var($dsv, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Cannot validate disease severity value as an integer');
        }

        if ($dsv < 0 || $dsv > 4) {
            throw new \RangeException('Disease severity value must be between 0 and 4');
        }

        return $dsv;
    }

    /**
     * Given an array of objects that allow calculation of DSVs, calculate the
     * mean temperature and leaf-wetting time of that group of hourlies.
     *
     * @param array $hourlies
     * @param int $threshold
     *
     * @return array
     */
    public static function calculateTemperatureAndLeafWettingTime(array $hourlies, $threshold)
    {
        $leafWettingTime = 0;
        $sumTemperature = 0;

        foreach ($hourlies as $hourly) {
            if (!($hourly instanceof DsvCalculableInterface)) {
                throw new \RuntimeException('Hourly is missing implementation detail.');
            }

            if ($hourly->getRelativeHumidity() > $threshold) {
                $leafWettingTime += 1;
            }

            $sumTemperature += $hourly->getTemperature();
        }

        $meanTemperature = $sumTemperature / count($hourlies);

        return [$meanTemperature, $leafWettingTime];
    }
}
