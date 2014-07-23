<?php

namespace PlantPath\Bundle\VDIFNBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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

        $station = $em
            ->getRepository('PlantPathVDIFNBundle:Station')
            ->findOneBy(['usaf' => $usaf, 'wban' => $wban]);

        $weather = $em
            ->getRepository('PlantPathVDIFNBundle:Weather\Observed\Daily')
            ->createQueryBuilder('d')
            ->where('d.usaf = :usaf')
            ->andWhere('d.wban = :wban')
            ->orderBy('d.time', 'DESC')
            ->setMaxResults(5)
            ->setParameter('usaf', $usaf)
            ->setParameter('wban', $wban)
            ->getQuery()
            ->getResult();

        return $this->render('PlantPathVDIFNBundle:Station:infobox.html.twig', [
            'station' => $station,
            'weather' => $weather,
        ]);
    }
}
