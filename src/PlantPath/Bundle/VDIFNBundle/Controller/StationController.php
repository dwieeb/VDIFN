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
            ->findBy([
                'country' => $country,
                'state' => $state,
            ]);

        if (!$entities) {
            throw $this->createNotFoundException('Unable to find daily weather data by specified criteria.');
        }

        return JsonResponse::create($entities);
    }
}
