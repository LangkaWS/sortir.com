<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\OptionsResolver\OptionsResolver;


class UserType extends AbstractType
{
    private $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $this->security->getUser();
        $builder
        
            ->add('username',TextType::class ,[
                'label' => 'Pseudo : ',
                'data' => $data->getPseudo()
                ])            
            ->add('firstName', TextType::class ,[
                'label' => 'Prénom : ',
                'data' => $data->getFirstName(),
                ])
            ->add('lastName', TextType::class ,[
                'label' => 'Nom : ',
                'data' => $data->getLastName(),
                ])
            ->add('phone', TextType::class,[
                'label' => 'Téléphone : ',
                'data' => $data->getPhone(),
                ])
            ->add('email', EmailType::class ,[
                'label' => 'Email : ',
                'data' => $data->getEmail(),
                ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'required' => false,
                'first_options'  => ['label' => 'Mot de passe : '],
                'second_options' => ['label' => 'Confirmation : '],
                ])
            ->add('campus')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
