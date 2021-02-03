<?php

namespace App\Form;

use App\Entity\Location;
use App\Entity\Outing;
use App\Entity\State;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('outingName', TextType::class, [
                'label' => 'Intitulé :',
                'label_attr' => [
                    'class' => 'label-custom'
                ],
                'attr' => [
                    'placeholder' => 'Ma super sortie'
                ],
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Date de la sortie :',
                'date_widget' => 'single_text',
                'date_label' => false,
                'time_label' => false,
                'attr' => [
                    'class' => 'form-custom'
                ],
                'label_attr' => [
                    'class' => 'label-custom'
                ]
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (minutes) :',
                'required' => false,
                'label_attr' => [
                    'class' => 'label-custom'
                ]
            ])
            ->add('registrationDeadLine', DateTimeType::class, [
                'label' => 'Date limite d\'inscription',
                'date_widget' => 'single_text',
                'date_label' => false,
                'time_label' => false,
                'attr' => [
                    'class' => 'form-custom'
                ],
                'label_attr' => [
                    'class' => 'label-custom'
                ]
            ])
            ->add('maxParticipants', IntegerType::class, [
                'label' => 'Nombre max de participants :',
                'label_attr' => [
                    'class' => 'label-custom'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description :',
                'required' => false,
                'label_attr' => [
                    'class' => 'label-custom'
                ],
                'attr' => [
                    'placeholder' => 'Mes super activités'
                ],
            ])
            ->add('location', EntityType::class, [
                'label' => 'Lieu :',
                'class' => Location::class,
                'label_attr' => [
                    'class' => 'label-custom'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Outing::class,
        ]);
    }
}
