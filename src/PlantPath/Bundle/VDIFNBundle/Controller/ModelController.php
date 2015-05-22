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
 * Weather\Daily controller.
 *
 * @Route("/model")
 */
class ModelController extends Controller
{
    /**
     * @Route("/", name="model_severity_legend", options={"expose"=true})
     * @Method("GET")
     */
    public function severityLegendAction(Request $request)
    {
        $crop = $request->query->get('crop');
        $infliction = $request->query->get('infliction');

        $class = DiseaseModel::getClassByCropAndDisease($crop, $infliction);

        return $this->render('PlantPathVDIFNBundle:Model:severity-legend.html.twig', [
            'thresholds' => $class::getThresholds(),
        ]);
    }
}
