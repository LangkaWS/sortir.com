<?php

namespace App\Form;

use App\Entity\Town;
use App\Entity\Outing;
use App\Entity\Location;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class OutingType extends AbstractType
{

    private $em;
    
    /**
     * The Type requires the EntityManager as argument in the constructor. It is autowired
     * in Symfony 3.
     * 
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    
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
                'widget' => 'single_text'
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
            ->add('campus', TextType::class, [
                'label' => 'Campus d\'origine :',
                'disabled' => true,
                'mapped' => false,
                'data' => $options['campus']->getName()
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));

    }

    protected function addElements(FormInterface $form, Town $town = null) {
        // 4. Add the town element
        $form->add('town', EntityType::class, array(
            'class' => Town::class,
            'required' => true,
            'placeholder' => 'Choisissez une ville',
            'mapped' => false,
            'label' => 'Ville :',
            'data' => $town
        ));

        $form->add('location', EntityType::class, array(
            'required' => true,
            'placeholder' => 'Choisissez la ville ...',
            'class' => Location::class,
            'label' => "Lieu : "
        ));

        $form->add('adress', TextType::class, [
            'label' => 'Adresse :',
            'disabled' => true,
            'mapped' => false
        ]);
        $form->add('longitude', TextType::class, [
            'label' => 'Longitude :',
            'disabled' => true,
            'mapped' => false
        ]);
        $form->add('latitude', TextType::class, [
            'label' => 'Latitude :',
            'disabled' => true,
            'mapped' => false
        ]);

    }

    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();
        
        // Search for selected City and convert it into an Entity
        $town = $this->em->getRepository(Town::class)->find($data['town']);
        
        $this->addElements($form, $town);
    }

    function onPreSetData(FormEvent $event) {
        $outing = $event->getData();
        $form = $event->getForm();

        // When you create a new person, the town is always empty
        $town = $outing->getLocation() ? $outing->getLocation()->getTown() : null;
        
        $this->addElements($form, $town);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Outing::class,
        ]);
        $resolver->setRequired('campus');

    }

}
