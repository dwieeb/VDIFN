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
     * Finds and displays a Weather\Daily entity.
     *
     * @Route("/{day}/{nwLat}/{nwLong}/{seLat}/{seLong}", name="weather_daily_bounding_box")
     * @Method("GET")
     */
    public function boundingBoxAction(Request $request, \DateTime $day, $nwLat, $nwLong, $seLat, $seLong)
    {
        $entity = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('PlantPathVDIFNBundle:Weather\Daily')
            ->getWithinBoundingBox($day, new Point($nwLat, $nwLong), new Point($seLat, $seLong));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find.');
        }

        return JsonResponse::create($entity);
    }
}
