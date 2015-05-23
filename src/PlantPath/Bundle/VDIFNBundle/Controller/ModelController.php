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
     * @Route("/severity-legend", name="model_severity_legend", options={"expose"=true})
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

    /**
     * @Route("/data", name="model_data", options={"expose"=true})
     * @Method("GET")
     */
    public function dataAction(Request $request)
    {
        $start = \DateTime::createFromFormat('Ymd', $request->query->get('start'));
        $end = \DateTime::createFromFormat('Ymd', $request->query->get('end'));
        $crop = $request->query->get('crop');
        $infliction = $request->query->get('infliction');

        if (empty($start) || empty($end) || empty($crop) || empty($infliction)) {
            return JsonResponse::create(['error' => 'Missing crucial query parameters.'], 400);
        }

        $class = DiseaseModel::getClassByCropAndDisease($crop, $infliction);

        $entities = $class::getDataByDateRange(
            $this->getDoctrine()->getManager(),
            $start,
            $end
        );

        if (empty($entities)) {
            throw $this->createNotFoundException('Unable to find daily weather data by specified criteria.');
        }

        return JsonResponse::create($entities);
    }
}
