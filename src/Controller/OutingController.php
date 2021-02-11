<?php

namespace App\Controller;


use App\Entity\Location;
use DateTime;
use DateInterval;
use App\Entity\State;
use App\Entity\Outing;
use App\Form\OutingType;
use App\Form\CancelOutingType;
use App\Form\FilterOutingType;
use App\Repository\CampusRepository;
use App\Repository\StateRepository;
use App\Repository\OutingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;


/**
 * @Route("/outing")
 */
class OutingController extends AbstractController
{
    /**
     * @Route("/", name="outing_index", methods={"GET"})
     */
    public function index(OutingRepository $outingRepository, CampusRepository $campusRepository): Response
    {
        return $this->render('outing/index.html.twig', [
            'outings' => $outingRepository->findByNotArchived(1),
            'campusList' => $campusRepository->findAll()
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
        if ($this->getUser()->getId() === $outing->getOrganizer()->getId()) {
          
          if ($outing->getState()->getId() === 1) {
            $form = $this->createForm(OutingType::class, $outing, [
                'campus' => $outing->getCampus()
                ]);
            $form->handleRequest($request);

            if ($request->request->get('edit') == 'publish'){
                $stateRepo = $this->getDoctrine()->getRepository(State::class);
                $outing->setState($stateRepo->find(2));
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
    
                return $this->redirectToRoute('outing_show', [
                    'id' => $outing->getId()
                ]);
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
          } else {
            $this->addFlash('warning', "Cette sortie n'est pas éditable.");
            return $this->redirectToRoute('app_home');
          }

        } else {
            $this->addFlash('warning', "Accès refusé : vous n'êtes pas l'organisateur de cette sortie et/ou.");
            return $this->redirectToRoute('app_home');
        }

    }

    /**
     * @Route("/{id}/cancel", name="outing_cancel", methods={"GET","POST"})
     */
    public function cancel(Request $request, Outing $outing): Response
    {

        if ($this->getUser()->getId() === $outing->getOrganizer()->getId()) {

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

        } else {
            $this->addFlash('warning', "Accès refusé : vous n'êtes pas l'organisateur de cette sortie.");
            return $this->redirectToRoute('outing_show', [
                    'id' => $outing->getId()
                ]);
        }
    }

    /**
     * @Route("/register/{id}", name="outing_register", methods={"POST"})
     */
    public function addParticipant(Outing $outing): Response
    {
        if ($outing->getRegistrationDeadLine()->getTimestamp() > time() && count($outing->getParticipants()) < $outing->getMaxParticipants()) {
            $outing->addParticipant($this->getUser());
            if (count($outing->getParticipants()) === $outing->getMaxParticipants()) {
                $stateRepo = $this->getDoctrine()->getRepository(State::class);
                $outing->setState($stateRepo->find(3));
            }
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Votre inscription à la sortie a bien été enregistrée');
            return $this->redirectToRoute('app_home');
        } else {
            $this->addFlash('warning', "Votre participation n'a pas pu être enregistrée car la date limite d'inscription est dépassée ou le nombre maximum de participants a été atteint.");
            return $this->redirectToRoute('app_home');
        }

    }

    /**
     * @Route("/unregister/{id}", name="outing_unregister", methods={"POST"})
     */
    public function removeParticipant(Outing $outing): Response
    {
        if ($outing->getStartDate() > new \DateTime('now')) {
            $outing->removeParticipant($this->getUser());
            if ($outing->getRegistrationDeadLine() > new DateTime() && count($outing->getParticipants()) < $outing->getMaxParticipants()) {
                $stateRepo = $this->getDoctrine()->getRepository(State::class);
                $outing->setState($stateRepo->find(2));
            }
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Votre désinscription de la sortie a bien été enregistrée.');
            return $this->redirectToRoute('app_home');
        } else {
            $this->addFlash('warning', "Vous ne pouvez plus vous désinscrire de cette sortie, elle a déjà commencé.");
            return $this->redirectToRoute('app_home');
        }
        
    }

}
