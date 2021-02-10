<?php

namespace App\Controller;

use App\Entity\State;
use App\Entity\Outing;
use App\Form\OutingType;
use App\Form\CancelOutingType;
use App\Form\FilterOutingType;
use App\Repository\StateRepository;
use App\Repository\OutingRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Json;

/**
 * @Route("/outing")
 */
class OutingController extends AbstractController
{
    /**
     * @Route("/", name="outing_index", methods={"GET"})
     */
    public function index(OutingRepository $outingRepository): Response
    {
        return $this->render('outing/index.html.twig', [
            'outings' => $outingRepository->findAll(),
        ]);
    }

    /**
     * @Route("/filter", name="outing_filter", methods={"POST"})
     */
    public function filter(OutingRepository $outingRepository, Request $request)
    {
        $campus = $request->request->get('campus');
        $nameContains = $request->request->get('outingNameContains');
        $minDate = $request->request->get('minDate');
        $maxDate = $request->request->get('maxDate');
        $isOrganizer = $request->request->get('isOrganizer');
        $isParticipant = $request->request->get('isParticipant');
        $isNotParticipant = $request->request->get('isNotParticipant');
        $isPassed = $request->request->get('isPassed');
        $user = $this->getUser();

        $outings = $outingRepository->findWithFilter($campus, $nameContains, $minDate, $maxDate, $isOrganizer, $isParticipant, $isNotParticipant, $isPassed, $user);

        return new Response(json_encode($outings));
    }

    

    /**
     * @Route("/new", name="outing_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $stateRepo = $this->getDoctrine()->getRepository(State::class);

        $outing = new Outing();
        $form = $this->createForm(OutingType::class, $outing);

        $outing->setOrganizer($this->getUser());
        $outing->setCampus($this->getUser()->getCampus());
        $outing->setState($stateRepo->find(1));

        $form = $this->createForm(OutingType::class, $outing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($outing);
            $entityManager->flush();


            $this->addFlash('success', "Votre sortie a bien été créée !");
            return $this->redirectToRoute('outing_show', [
                'id' => $outing->getId()
            ]);

        }

        return $this->render('outing/new.html.twig', [
            'outing' => $outing,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="outing_show", methods={"GET"})
     */
    public function show(Outing $outing): Response
    {
        return $this->render('outing/show.html.twig', [
            'outing' => $outing,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="outing_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Outing $outing): Response
    {
        $form = $this->createForm(OutingType::class, $outing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('outing_index');
        }

        return $this->render('outing/edit.html.twig', [
            'outing' => $outing,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/cancel", name="outing_cancel", methods={"GET","POST"})
     */
    public function cancel(Request $request, Outing $outing): Response
    {

        $stateRepo = $this->getDoctrine()->getRepository(State::class);
        $form = $this->createForm(CancelOutingType::class, $outing);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $outing->setState($stateRepo->find(6));
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('outing/cancel.html.twig', [
            'outing' => $outing,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register/{id}", name="outing_register", methods={"POST"})
     */
    public function addParticipant(Outing $outing): Response
    {
        $outing->addParticipant($this->getUser());
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', 'Votre inscription a bien été enregsitrée');
        return $this->redirectToRoute('app_home');
    }
}
