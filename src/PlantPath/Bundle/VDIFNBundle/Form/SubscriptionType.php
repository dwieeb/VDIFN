<?php

namespace PlantPath\Bundle\VDIFNBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use PlantPath\Bundle\VDIFNBundle\Geo\Crop;
use PlantPath\Bundle\VDIFNBundle\Geo\Infliction;
use PlantPath\Bundle\VDIFNBundle\Geo\Threshold;

class SubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $thresholds = Threshold::getFormChoices();
        unset($thresholds[Threshold::VERY_LOW]);

        $builder
            ->add('latitude', 'hidden')
            ->add('longitude', 'hidden')
            ->add('crop', 'choice', [
                'choices' => Crop::getFormChoices(),
                'required' => true,
            ])
            ->add('infliction', 'choice', [
                'choices' => Infliction::getFormChoices(),
                'required' => true,
            ])
            ->add('emergenceDate', 'date')
            ->add('threshold', 'choice', [
                'choices' => $thresholds,
                'label' => 'Severity threshold',
                'required' => true,
            ])
            ->add('save', 'submit', ['label' => 'Subscribe']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'PlantPath\Bundle\VDIFNBundle\Entity\Subscription',
        ]);
    }

    public function getName()
    {
        return 'subscription';
    }
}
