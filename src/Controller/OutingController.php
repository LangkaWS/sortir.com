<?php

namespace App\Controller;


use App\Entity\Location;
use DateTime;
use DateInterval;
use App\Entity\State;
use App\Entity\Outing;
use App\Form\OutingType;
use App\Form\CancelOutingType;
use App\Repository\StateRepository;
use App\Repository\OutingRepository;
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
            'outings' => $outingRepository->findByNotArchived(1),
        ]);
    }

    /**
     * @Route("/new", name="outing_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $stateRepo = $this->getDoctrine()->getRepository(State::class);

        $organizer = $this->getUser();
        $campus = $organizer->getCampus();

        $outing = new Outing();
        $outing->setOrganizer($organizer);
        $outing->setCampus($campus);
        $form = $this->createForm(OutingType::class, $outing, [
            'campus' => $campus
        ]);
        $request->request->get('create') == 'create'
            ? $outing->setState($stateRepo->find(1))
            : $outing->setState($stateRepo->find(2));
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
            'action' => 'new'
        ]);
    }

    /**
     * @Route("/get-locations-from-town", name="outing_list_locations")
     */
    public function listLocationsOfTownAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $locationsRepo = $em->getRepository(Location::class);
        $townid = $request->query->get('townid');
        
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
     * @Route("/get-location-informations", name="outing_location_informations")
     */
    public function locationInformations(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $locationsRepo = $em->getRepository(Location::class);
        $locationId = $request->query->get('locationId');
        $responseArray = array();
        $responseArray['adress'] = '';
        $responseArray['latitude'] = '';
        $responseArray['longitude'] = '';
        $location = $locationsRepo->find($locationId);
        if ($locationId) {
            $responseArray['adress'] = $location->getAdress();
            $responseArray['latitude'] = $location->getLatitude();
            $responseArray['longitude'] = $location->getLongitude();
        }
        return new JsonResponse($responseArray);
    }

    /**
     * @Route("/{id}", name="outing_show", methods={"GET"})
     */
    public function show(Outing $outing): Response
    {
        if($outing->getStartDate() <= (new DateTime())->sub(new DateInterval("P1M"))) {
            $this->addFlash('warning', "Cette sortie est archivée, elle n'est plus consultable.");
            return $this->redirectToRoute('app_home');
        }
        
        return $this->render('outing/show.html.twig', [
            'outing' => $outing,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="outing_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Outing $outing): Response
    {
        $form = $this->createForm(OutingType::class, $outing, [
            'campus' => $outing->getCampus()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('outing_index');
        }

        if($outing->getStartDate() <= (new DateTime())->sub(new DateInterval("P1M"))) {
            $this->addFlash('warning', "Cette sortie est archivée, elle n'est plus consultable.");
            return $this->redirectToRoute('app_home');
        }

        return $this->render('outing/edit.html.twig', [
            'outing' => $outing,
            'form' => $form->createView(),
            'action' => 'edit'
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

        if($outing->getStartDate() <= (new DateTime())->sub(new DateInterval("P1M"))) {
            $this->addFlash('warning', "Cette sortie est archivée, elle n'est plus consultable.");
            return $this->redirectToRoute('app_home');
        }

        return $this->render('outing/cancel.html.twig', [
            'outing' => $outing,
            'form' => $form->createView(),
            'action' => 'cancel'
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
