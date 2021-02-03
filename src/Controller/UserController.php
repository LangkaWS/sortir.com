<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\RegisterType;
use App\Security\Authenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/register", name="user_register")
     */
    public function register(
        Request $request, 
        EntityManagerInterface $em, 
        UserPasswordEncoderInterface $encoder, 
        Authenticator $login, 
        GuardAuthenticatorHandler $guard
        ): Response
    {
        $user = new User();
        $registerForm = $this->createForm(RegisterType::class, $user);

        $registerForm->handleRequest($request);
        if($registerForm->isSubmitted() && $registerForm->isValid()) {
            $hashed = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashed);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'L\'inscription a bien été validée.');

            return $guard->authenticateUserAndHandleSuccess($user, $request, $login, 'main');
            //return $this->redirectToRoute("app_home", ["user" => $user]);
        }

        return $this->render('user/register.html.twig', [
            'registerForm' => $registerForm->createView()
        ]);
    }

     /**
      *
      * @param Request $request
      * @param EntityManagerInterface $em
      * @return Response
      * @Route("/profile/{username}/edit", name="user_profile", methods={"GET","POST"})
      */
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder): Response
    {
        $user = $this->getUser();        
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            
            $em->flush();

            $this->addFlash('success', 'La modification du profil à bien été prise en compte.');
            return $this->redirectToRoute("app_home");
        }
        return $this->render('user/edit.html.twig', [
            'controller_name' => 'ManageProfileController',
            'profile' => $form->createView(),
        ])
        ;
    }
}
