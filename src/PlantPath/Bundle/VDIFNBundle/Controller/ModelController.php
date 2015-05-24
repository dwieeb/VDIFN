<?php

namespace PlantPath\Bundle\VDIFNBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PlantPath\Bundle\VDIFNBundle\Geo\AbstractInfliction;
use PlantPath\Bundle\VDIFNBundle\Geo\Disease;
use PlantPath\Bundle\VDIFNBundle\Geo\Model\DiseaseModel;

/**
 * Weather\Daily controller.
 *
 * @Route("/model")
 */
class ModelController extends Controller
{
    /**
     * @Route("/{crop}/inflictions/{infliction}/severity-legend", name="model_severity_legend", options={"expose"=true})
     * @Method("GET")
     */
    public function severityLegendAction(Request $request, $crop, $infliction)
    {
        $inflictionClass = AbstractInfliction::getClassBySlug($infliction);

        if ($inflictionClass::INFLICTION_TYPE == Disease::INFLICTION_TYPE) {
            $class = DiseaseModel::getClassByCropAndDisease($crop, $infliction);

            return $this->render('PlantPathVDIFNBundle:Model:severity-legend.html.twig', [
                'thresholds' => $class::getThresholds(),
            ]);
        }

        throw $this->createNotFoundException('Unknown infliction type.');
    }

    /**
     * @Route("/{crop}/inflictions/{infliction}/data", name="model_data", options={"expose"=true})
     * @Method("GET")
     */
    public function dataAction(Request $request, $crop, $infliction)
    {
        $inflictionClass = AbstractInfliction::getClassBySlug($infliction);

        if ($inflictionClass::INFLICTION_TYPE == Disease::INFLICTION_TYPE) {
            $start = \DateTime::createFromFormat('Ymd', $request->query->get('start'));
            $end = \DateTime::createFromFormat('Ymd', $request->query->get('end'));

            if (empty($start) || empty($end)) {
                return JsonResponse::create(['error' => 'Missing crucial query parameters.'], 400);
            }

            $modelClass = DiseaseModel::getClassByCropAndDisease($crop, $infliction);

            $entities = $modelClass::getDataByDateRange(
                $this->getDoctrine()->getManager(),
                $start,
                $end
            );

            if (empty($entities)) {
                throw $this->createNotFoundException('Unable to find daily weather data by specified criteria.');
            }

            return JsonResponse::create($entities);
        }

        throw $this->createNotFoundException('Unknown infliction type.');
    }
}
