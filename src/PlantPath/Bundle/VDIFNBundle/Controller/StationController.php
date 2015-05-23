<?php

namespace PlantPath\Bundle\VDIFNBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PlantPath\Bundle\VDIFNBundle\Geo\Model\DiseaseModel;

/**
 * Station controller.
 *
 * @Route("/stations")
 */
class StationController extends Controller
{
    /**
     * Finds and displays Stations by their respective country and state.
     *
     * @Route("/", name="stations_list", options={"expose"=true})
     * @Method("GET")
     */
    public function countryStateAction(Request $request)
    {
        $country = $request->query->get('country');
        $state = $request->query->get('state');

        if (!empty($country) && !empty($state)) {
            $entities = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('PlantPathVDIFNBundle:Station')
                ->getOpenByCountryAndState($country, $state);
        }

        if (empty($entities)) {
            throw $this->createNotFoundException('Unable to find stations by specified criteria.');
        }

        return JsonResponse::create($entities);
    }

    /**
     * Gets a station.
     *
     * @Route("/{usaf}-{wban}", name="stations_get", options={"expose"=true})
     */
    public function stationAction(Request $request, $usaf, $wban)
    {
        $em = $this->getDoctrine()->getManager();

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

        $station = $em
            ->getRepository('PlantPathVDIFNBundle:Station')
            ->findOneBy(['usaf' => $usaf, 'wban' => $wban]);

        $em = $this->getDoctrine()->getManager();
        $class = DiseaseModel::getClassByCropAndDisease($crop, $infliction);
        $weather = $class::getStationData($em, $usaf, $wban, $start, $end);

        return $this->render('PlantPathVDIFNBundle:Station:infobox.html.twig', [
            'station' => $station,
            'weather' => $weather,
        ]);
    }
}
