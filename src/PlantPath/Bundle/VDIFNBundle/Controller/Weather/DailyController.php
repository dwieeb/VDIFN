<?php

namespace PlantPath\Bundle\VDIFNBundle\Controller\Weather;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use PlantPath\Bundle\VDIFNBundle\Geo\Disease;
use PlantPath\Bundle\VDIFNBundle\Geo\Model\DiseaseModel;

/**
 * Weather\Daily controller.
 *
 * @Route("/weather/daily")
 */
class DailyController extends Controller
{
    /**
     * Finds data for a data point.
     *
     * @Route("/point/{latitude}/{longitude}", name="weather_daily_point", options={"expose"=true})
     * @Method("GET")
     */
    public function pointAction(Request $request, $latitude, $longitude)
    {
        $em = $this->getDoctrine()->getManager();

        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();

        $point = new Point($latitude, $longitude);
        $start = \DateTime::createFromFormat('Ymd', $request->query->get('start'));
        $end = \DateTime::createFromFormat('Ymd', $request->query->get('end'));
        $crop = $request->query->get('crop');
        $infliction = $request->query->get('infliction');

        if (empty($start) || empty($end) || empty($crop) || empty($infliction)) {
            return JsonResponse::create(['error' => 'Missing crucial query parameters.'], 400);
        }

        $minStart = clone $end;
        $minStart->modify('-10 days');
        $start = min($minStart, $start);

        $start->setTime(0, 0, 0);
        $end->setTime(0, 0, 0);

        $class = DiseaseModel::getClassByCropAndDisease($crop, $infliction);
        $weather = $class::getPointData($em, $point, $start, $end);

        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $subscription = $em
                ->getRepository('PlantPathVDIFNBundle:Subscription')
                ->createQueryBuilder('s')
                ->where('s.user = :user')
                ->andWhere('s.latitude = :latitude')
                ->andWhere('s.longitude = :longitude')
                ->setParameter('user', $user)
                ->setParameter('latitude', $point->getLatitude())
                ->setParameter('longitude', $point->getLongitude())
                ->getQuery()
                ->getOneOrNullResult();
        }

        return $this->render('PlantPathVDIFNBundle:ModelDataPoint:infobox.html.twig', [
            'point' => $point,
            'weather' => $weather,
            'subscription' => !empty($subscription) ? $subscription : null,
        ]);
    }
}
