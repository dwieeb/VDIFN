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
     * @Route("/{start}/{end}", name="weather_daily_date_range", options={"expose"=true})
     * @Method("GET")
     */
    public function dateRangeAction(Request $request, \DateTime $start, \DateTime $end)
    {
        $entities = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('PlantPathVDIFNBundle:Weather\Daily')
            ->getDsvAverageWithinDateRange($start, $end);

        if (!$entities) {
            throw $this->createNotFoundException('Unable to find daily weather data by specified criteria.');
        }

        return JsonResponse::create($entities);
    }
}
