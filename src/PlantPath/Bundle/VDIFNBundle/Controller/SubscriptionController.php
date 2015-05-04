<?php

namespace PlantPath\Bundle\VDIFNBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PlantPath\Bundle\VDIFNBundle\Entity\Subscription;
use PlantPath\Bundle\VDIFNBundle\Geo\Point;

class SubscriptionController extends Controller
{
    /**
     * @Route("/subscriptions/form", name="subscriptions_form", options={"expose"=true})
     * @Template()
     */
    public function formAction(Request $request)
    {
        $subscription = new Subscription();

        $form = $this->createFormBuilder($subscription)
            ->add('latitude', 'hidden')
            ->add('longitude', 'hidden')
            ->add('threshold', 'choice', [
                'choices' => [
                    'very_high' => 'Very High',
                    'high' => 'High',
                    'medium' => 'Medium',
                    'low' => 'Low',
                ],
                'label' => false,
                'required' => true,
            ])
            ->add('save', 'submit', ['label' => 'Subscribe'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $latitude = $form->get('latitude')->getData();
            $longitude = $form->get('longitude')->getData();

            $data = $form->getData();
            $user = $this->get('security.context')->getToken()->getUser();
            $point = new Point($latitude, $longitude);

            $data
                ->setUser($user)
                ->setPoint($point);

            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            $em->flush();

            return new Response();
        }

        return $this->render('PlantPathVDIFNBundle:Subscription:form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
