<?php

namespace PlantPath\Bundle\VDIFNBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PlantPath\Bundle\VDIFNBundle\Geo\Crop;
use PlantPath\Bundle\VDIFNBundle\Geo\Infliction;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index", options={"expose"=true})
     * @Template()
     */
    public function indexAction()
    {
        $weather = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('PlantPathVDIFNBundle:Weather\Daily')
            ->getLatestOne();

        return $this->render('PlantPathVDIFNBundle:Default:index.html.twig', [
            'latest' => $weather,
            'crops' => Crop::getFormChoices(),
            'inflictions' => Infliction::getFormChoices(),
        ]);
    }

    /**
     * @Route("/user-links", name="user_links", options={"expose": true})
     * @Template()
     */
    public function userLinksAction()
    {
        return $this->render('PlantPathVDIFNBundle:Default:user-links.html.twig');
    }
}
