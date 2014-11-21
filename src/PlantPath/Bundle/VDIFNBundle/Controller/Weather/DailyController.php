<?php

namespace PlantPath\Bundle\VDIFNBundle\Controller\Weather;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PlantPath\Bundle\VDIFNBundle\Geo\Point;

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
     * @Route("/", name="weather_daily_date_range", options={"expose"=true})
     * @Method("GET")
     */
    public function dateRangeAction(Request $request)
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');
        $crop = $request->query->get('crop');
        $infliction = $request->query->get('infliction');

        if (!empty($start) && !empty($end) && !empty($crop) && !empty($infliction)) {
            $start = new \DateTime($start);
            $end = new \DateTime($end);

            $entities = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('PlantPathVDIFNBundle:Weather\Daily')
                ->getDsvSumsWithinDateRange($start, $end);
        }

        if (empty($entities)) {
            throw $this->createNotFoundException('Unable to find daily weather data by specified criteria.');
        }

        return JsonResponse::create($entities);
    }

    /**
     * Finds data for a data point.
     *
     * @Route("/point/{latitude}/{longitude}", name="weather_daily_point", options={"expose"=true})
     * @Method("GET")
     */
    public function pointAction(Request $request, $latitude, $longitude)
    {
        $point = new Point($latitude, $longitude);

        $startDate = \DateTime::createFromFormat('Ymd', $request->query->get('startDate'));
        $endDate = \DateTime::createFromFormat('Ymd', $request->query->get('endDate'));

        if (false === $startDate || false === $endDate) {
            throw new \InvalidArgumentException('startDate and endDate are invalid.');
        }

        $minStartDate = clone $endDate;
        $minStartDate->modify('-10 days');
        $startDate = min($minStartDate, $startDate);

        $startDate->setTime(0, 0, 0);
        $endDate->setTime(0, 0, 0);

        $weather = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('PlantPathVDIFNBundle:Weather\Daily')
            ->createQueryBuilder('d')
            ->where('d.time BETWEEN :start AND :end')
            ->andWhere('d.latitude = :latitude')
            ->andWhere('d.longitude = :longitude')
            ->orderBy('d.time', 'DESC')
            ->setParameter('latitude', $point->getLatitude())
            ->setParameter('longitude', $point->getLongitude())
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getResult();

        return $this->render('PlantPathVDIFNBundle:ModelDataPoint:infobox.html.twig', [
            'point' => $point,
            'weather' => $weather,
        ]);
    }
}
