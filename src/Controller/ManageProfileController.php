<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\MyProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ManageProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="app_profile")
     */
    public function myProfile(Request $request, EntityManagerInterface $em): Response
    {
        $user = new User();
        $profile = $this->createForm(MyProfileType::class);
        $profile->handleRequest($request);

        if($profile->isSubmitted() && $profile->isValid()){
                $em->persist($user);
                $em->flush();
        }
        return $this->render('manage_profile/myProfile.html.twig', [
            'controller_name' => 'ManageProfileController',
            'profile' => $profile->createView(),
        ]);
    }
}
