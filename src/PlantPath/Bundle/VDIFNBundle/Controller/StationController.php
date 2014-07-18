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
     * @Route("/{country}/{state}", name="stations_country_state", options={"expose"=true})
     * @Method("GET")
     */
    public function countryStateAction(Request $request, $country, $state)
    {
        $entities = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('PlantPathVDIFNBundle:Station')
            ->getOpenByCountryAndState($country, $state);

        if (!$entities) {
            throw $this->createNotFoundException('Unable to find daily weather data by specified criteria.');
        }

        return JsonResponse::create($entities);
    }

    /**
     * Gets a station.
     *
     * @Route("/{usaf}/{wban}/{start}/{end}", name="stations_single", options={"expose"=true})
     */
    public function stationAction(Request $request, $usaf, $wban, \DateTime $start, \DateTime $end)
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
            ->andWhere('d.time BETWEEN :start AND :end')
            ->orderBy('d.time', 'DESC')
            ->setParameter('usaf', $usaf)
            ->setParameter('wban', $wban)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        return $this->render('PlantPathVDIFNBundle:Station:infobox.html.twig', [
            'station' => $station,
            'weather' => $weather,
        ]);
    }
}
