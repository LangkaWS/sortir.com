<?php

namespace App\Form;

use App\Entity\Outing;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('outingName')
            ->add('startDate')
            ->add('duration')
            ->add('registrationDeadLine')
            ->add('maxParticipants')
            ->add('description')
            ->add('state')
            ->add('location')
            ->add('campus')
            ->add('organizer')
            ->add('participants')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Outing::class,
        ]);
    }
}
