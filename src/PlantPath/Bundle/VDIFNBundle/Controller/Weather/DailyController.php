<?php

namespace PlantPath\Bundle\VDIFNBundle\Controller\Weather;

use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PlantPath\Bundle\VDIFNBundle\Entity\Weather\Daily;

/**
 * Weather\Daily controller.
 *
 * @Route("/weather/daily")
 */
class DailyController extends Controller
{
    /**
     * Finds and displays Weather\Daily entities.
     *
     * @Route("/{day}/{nwLat}/{nwLong}/{seLat}/{seLong}", name="weather_daily_bounding_box", options={"expose"=true})
     * @Method("GET")
     */
    public function boundingBoxAction(Request $request, \DateTime $day, $nwLat, $nwLong, $seLat, $seLong)
    {
        $entities = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('PlantPathVDIFNBundle:Weather\Daily')
            ->getWithinBoundingBox(
                $day,
                new Point($nwLat, $nwLong),
                new Point($seLat, $seLong),
                ['d.time', 'd.latitude', 'd.longitude', 'd.dsv']
            );

        if (!$entities) {
            throw $this->createNotFoundException('Unable to find.');
        }

        foreach ($entities as &$entity) {
            $entity['time'] = $entity['time']->format('Ymd');
        }

        return JsonResponse::create($entities);
    }
}
