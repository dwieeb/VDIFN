<?php

namespace PlantPath\Bundle\VDIFNBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return new Response(sprintf($this->container->getParameter('noaa_url'), '20140204'));
    }

    /**
     * @Route("/hello/{first}/{last}")
     * @Template()
     */
    public function helloAction($last, $first)
    {
        return array(
            'first' => $first,
            'last' => $last,
        );
    }
}
