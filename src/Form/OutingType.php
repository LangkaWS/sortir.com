<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Location;
use App\Entity\Outing;
use App\Entity\Town;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('outingName', TextType::class, [
                'label' => 'Intitulé :',
                'attr' => [
                    'placeholder' => 'Ma super sortie'
                ],
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Date de la sortie :',
                'date_widget' => 'single_text',
                'date_label' => false,
                'time_label' => false,
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (minutes) :',
                'required' => false,
            ])
            ->add('registrationDeadLine', DateType::class, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text'
            ])
            ->add('maxParticipants', IntegerType::class, [
                'label' => 'Nombre max de participants :',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description :',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Mes super activités'
                ],
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus d\'origine :',
                'class' => Campus::class,
                'disabled' => true
            ])
            ->add('town', EntityType::class, [
                'class' => Town::class,
                'mapped' => false,
                'label' => 'Ville :',
                'choice_label' => function($choice){ return $choice->getName();},
            ])
        ;

        $formModifier = function(FormInterface $form, Town $town = null){
            $locations = $town === null ? [] : $town->getLocations();

            $form->add('location', EntityType::class, [
                'class' => Location::class,
                'placeholder' => '',
                'choices' => $locations
            ]);
        };

        $builder->addEventListener(
          FormEvents::PRE_SET_DATA,
          function (FormEvent $event) use ($formModifier) {
              $outing = $event->getData();
              dump($outing);
              if ($outing->getLocation() !== null) {
                  $town = $outing->getLocation()->getTown();
              }
              if(isset($town)){
                  $formModifier($event->getForm(), $town);
              } else {
                  $formModifier($event->getForm());
              }
          }
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $outing = $event->getData();
                dump($outing);
            }
        );

        $builder->get('town')->addEventListener(
          FormEvents::POST_SUBMIT,
          function (FormEvent $event) use ($formModifier){
              $town = $event->getForm()->getData();
              $formModifier($event->getForm()->getParent(), $town);
          }
        );
     }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Outing::class,
        ]);

    }

}
