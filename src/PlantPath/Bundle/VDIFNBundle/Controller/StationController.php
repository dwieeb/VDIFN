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
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getResult();

        return $this->render('PlantPathVDIFNBundle:Station:infobox.html.twig', [
            'station' => $station,
            'weather' => $weather,
        ]);
    }
}
