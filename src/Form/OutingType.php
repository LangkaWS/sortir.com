<?php

namespace App\Form;

use App\Entity\Town;
use App\Entity\Campus;
use App\Entity\Outing;
use App\Entity\Location;
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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));

        /* $formModifier = function(FormInterface $form, Town $town = null){
            dump($town);
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
        ); */
    }

    protected function addElements(FormInterface $form, Town $town = null) {
        // 4. Add the province element
        $form->add('town', EntityType::class, array(
            'required' => true,
            'class' => Town::class,
            'mapped' => false,
            'label' => 'Ville :',
            'choice_label' => function($choice){ return $choice->getName();},
        ));
        
        // Neighborhoods empty, unless there is a selected City (Edit View)
        $location = array();
        
        // If there is a city stored in the Person entity, load the neighborhoods of it
        if ($town) {
            // Fetch Neighborhoods of the City if there's a selected city
            $locationRepo = $this->em->getRepository(Location::class);
            
            /* $location = $locationRepo->createQueryBuilder("q")
                ->where("q.town = :townid")
                ->setParameter("townid", $town->getId())
                ->getQuery()
                ->getResult(); */
        }
        
        // Add the Neighborhoods field with the properly data
        $form->add('location', EntityType::class, array(
            'required' => true,
            'placeholder' => 'Select a City first ...',
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

        // When you create a new person, the City is always empty
        $town = $outing->getLocation() ? $outing->getLocation()->getTown() : null;
        
        $this->addElements($form, $town);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Outing::class,
        ]);

    }

}
