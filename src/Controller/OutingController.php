<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Outing;
use App\Entity\State;
use App\Repository\StateRepository;
use App\Form\OutingType;
use App\Repository\OutingRepository;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/new", name="outing_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $stateRepo = $this->getDoctrine()->getRepository(State::class);

        $outing = new Outing();
        $outing->setCampus($this->getUser()->getCampus());
        $outing->setOrganizer($this->getUser());
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
     * @Route("/get-locations-from-town", name="outing_list_locations")
     */
    public function listLocationsOfTownAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $locationsRepo = $em->getRepository(Location::class);

        $townid = $request->get('townid');
        
        $locations = $locationsRepo->findByTown($townid);
        
        $responseArray = array();

        foreach($locations as $location) {
            $responseArray[] = array(
                "id" => $location->getId(),
                "name" => $location->getName()
            );
        }

        return new JsonResponse($responseArray);
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
     * @Route("/{id}", name="outing_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Outing $outing): Response
    {
        if ($this->isCsrfTokenValid('delete'.$outing->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($outing);
            $entityManager->flush();
        }

        return $this->redirectToRoute('outing_index');
    }
}
