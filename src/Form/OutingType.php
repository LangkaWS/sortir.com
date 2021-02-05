<?php

namespace App\Form;

use App\Entity\Town;
use App\Entity\Campus;
use App\Entity\Outing;
use App\Entity\Location;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
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
            /*
            ->add('organizer', HiddenType::class, [
                'data' => $options['organizer']
            ])
            */
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
            ->add('campusShown', TextType::class, [
                'label' => 'Campus d\'origine :',
                'disabled' => true,
                'mapped' => false,
                'data' => $options['campusShown']->getName()
            ])
            /*
            ->add('campus', HiddenType::class, [
                'data' => $options['campus']
            ])

            ->add('location', EntityType::class, array(
                'required' => true,
                'class' => Location::class
            ));
*/
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));

    }

    protected function addElements(FormInterface $form, Town $town = null) {
        // 4. Add the town element
        $form->add('town', EntityType::class, array(
            'class' => Town::class,
            'required' => false,
            'placeholder' => 'Choisissez une ville',
            'mapped' => false,
            'label' => 'Ville :',
            'choice_label' => function($choice){ return $choice->getName();},
        ));

        $location = array();

        $form->add('location', EntityType::class, array(
            'required' => true,
            'placeholder' => 'Choisissez d\'abord la ville ...',
            'class' => Location::class,
            'choices' => $location
        ));
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
        $resolver->setRequired('campusShown');
        $resolver->setRequired('organizer');

    }

}
