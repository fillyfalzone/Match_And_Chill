<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventFormType;
use App\Repository\EventRepository;
use App\HttpClient\OpenLigaDBClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventsController extends AbstractController
{

    // Liste des événements
    #[Route('/events', name: 'app_events')]
    public function index(OpenLigaDBClient $httpClient, EventRepository $eventRepository, Request $request): Response
    {   
        // Récupérer le tableau de la Bundesliga
        $table = $httpClient->getTable();

        // Récupération des paramètres de filtrage depuis la requête.
        $teamIdInput = $request->query->get('teamId');
        $statusInput = $request->query->get('status', false); // 'false' indique aucun filtre de statut par défaut.

        // filtrer les inputs
        $teamId = filter_var($teamIdInput, FILTER_VALIDATE_INT);
        $status = filter_var($statusInput, FILTER_VALIDATE_BOOLEAN);

        // Récupère tous les événements de la base de données, triés par date de commencement.
        $events = $eventRepository->findBy([], ['startDate' => 'ASC']);
        
        // Initialisation du tableau qui va contenir les événements filtrés.
        $eventsSort = [];

        // Boucle sur chaque événement pour déterminer s'il correspond aux critères de filtrage.
        foreach ($events as $event) {
            // Initialisation de la variable pour décider de l'inclusion de l'événement.
            $includeEvent = false;

            // Chargement des données du match de l'API externe si un ID d'équipe est spécifié.
            if ($teamId) {
                $match = $httpClient->getMatchById($event->getMatchId());

                // Utilisation de l'opérateur de coalescence nulle pour éviter l'erreur sur les clés inexistantes.
                $team1Id = $match['team1']['teamId'] ?? null;
                $team2Id = $match['team2']['teamId'] ?? null;
        
                // Vérification si l'événement correspond à l'équipe spécifiée.
                $isTeamMatch = ($team1Id == $teamId || $team2Id == $teamId);
            } else {
                // Si aucun ID d'équipe n'est spécifié, l'événement correspond par défaut à ce critère.
                $isTeamMatch = true;
            }

            // Vérification si l'événement correspond au statut spécifié.
            $isStatusMatch = $status !== false ? $event->isIsLocked() == $status : true;

            // Inclusion de l'événement si et seulement si il correspond aux deux critères.
            if ($isTeamMatch && $isStatusMatch) {
                $eventsSort[] = $event;
            }
        }
        
        return $this->render('events/events.html.twig', [
            'table' => $table,
            'eventsSort' => $eventsSort,
        ]);
    }

    // list events sorted by date or status
    #[Route('/events/sorted', name: 'app_events_sorted', methods: ['POST'])]
    public function sorted(EventRepository $eventRepository, OpenLigaDBClient $httpClient, Request $request): JsonResponse
    {   
        // Décodage des données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        $teamId = $data['teamId'] ?? null;

        // On recupère les matchs d'une équipe
        $matchs = $httpClient->getMatchByTeamId($data['teamId']);

        if($teamId){
            // On recupère les événements associés à ces matchs
            foreach ($matchs as $match) {
                $event = $eventRepository->findOneBy(['matchId' => $teamId]);
                $events[] = $event;
            }
        }

        return new JsonResponse('events', $events);
    }

    // Récupérer l'id de l'équipe
    #[Route('/send/teamId', name: 'app_send_teamId', methods: ['POST'])]
    public function getTeamId(Request $request): JsonResponse
    {
        // Décodage des données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);
        $teamId = $data['teamId'];

        return new JsonResponse('teamId', $teamId);
    }
    

    // Create new or edit event
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour afficher les détails d\'un événement')]
    #[Route('/events/new', name: 'app_events_new')]
    #[Route('/events/edit/{id}', name: 'app_events_edit')]
    public function newedit(Event $event = null, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, Request $request): Response
    {
        
       
        // Création d'un nouvel objet Event si aucun n'est fourni
        if (!$event){
            $event = new Event();
        }
        
        // Récupérer l'id de l'événement s'il existe
        $id = $event->getId();
        
        // Création du formulaire
        $form = $this->createForm(EventFormType::class, $event);

        // Traitement de la requête
        $form->handleRequest($request); 
       
        if ($form->isSubmitted() && $form->isValid()) {

            // Ajouter les informations manquantes
            $event->setUser($tokenStorage->getToken()->getUser());
            $event->setCreationDate(new \DateTime());
            $event->setIsLocked("0");

            // Enregistrer l'événement
            $entityManager->persist($event);
            $entityManager->flush();

            // Rediriger vers la page de l'événement
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        // Affichage du formulaire
        return $this->render('events/newedit.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
        ]);
    }


    // Delete event
    #[Route('/events/delete/{id}', name: 'app_events_delete')]
    public function delete(EntityManagerInterface $entityManager, EventRepository $eventRepository, int $id): Response
    {
        // Récupérer l'événement
        $event = $eventRepository->find($id);

        // Supprimer l'événement
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->redirectToRoute('app_events');
    }

    // Show event
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour afficher les détails d\'un événement')]
    #[Route('/events/show/{id}', name: 'app_events_show')]
    public function show(OpenLigaDBClient $httpClient, EventRepository $eventRepository, TokenStorageInterface $tokenStorage, int $id ): Response
    {   
        // Récupérer l'événement
        $event = $eventRepository->find($id);
        // Récupérer l'utilisateur connecté
        $user = $tokenStorage->getToken()->getUser();

        // Vérifier si l'utilisateur participe à l'événement
        $participates =  $event->getUsersParticipate();

        // On recupère l'utilisateur qui participe à l'événement
        $participate = null; 
        foreach ($participates as $participate) {
            
            if($participate == $user){
                $participate;
                break;
            }else{
                $participate;
            }
        }
        // On recupère le nombre de places disponibles
        $availablePlaces = $event->getNumberOfPlaces() - count($event->getUsersParticipate());

        // On recupère l'id du match
        $matchId = $event->getMatchId(); 
        // On recupère les informations du match
        $match = $httpClient->getMatchById($matchId);
        

        // Affichage de la page de l'événement
        return $this->render('events/show.html.twig', [
            'event' => $event,
            'match' => $match,
            'participate' => $participate,
            'availablePlaces' => $availablePlaces,
        ]);
    }

    // Participer à un événement
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour participer à un événement')]
    #[Route('/events/participate/{id}', name: 'app_events_participate')]
    public function participate(Event $event, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, int $id): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $tokenStorage->getToken()->getUser();

        $nbPlaces = $event->getNumberOfPlaces();
        $nbParticipants = count($event->getUsersParticipate());

        // Vérifier si l'événement est complet
        if ($nbParticipants >= $nbPlaces) {
            $this->addFlash('error', 'L\'événement est complet');
            return $this->redirectToRoute('app_events_show', ['id' => $id]);
        } elseif($nbPlaces = $nbParticipants + 1) {
            // Ajouter l'utilisateur à la liste des participants
            $event->addUserParticipate($user);
            $event->setIsLocked("1");
            // Enregistrer l'événement
            $entityManager->persist($event);
        } else {
            // Ajouter l'utilisateur à la liste des participants
             $event->addUserParticipate($user);
            // Enregistrer l'événement
            $entityManager->persist($event);
        }
        $entityManager->flush();

        // Rediriger vers la page de l'événement
        return $this->redirectToRoute('app_events_show', ['id' => $id]);
    }

    // Annuler la participation à un événement
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour annuler votre participation à un événement')]
    #[Route('/events/unparticipate/{id}', name: 'app_events_unparticipate')]
    public function unparticipate(Event $event, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, int $id): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $tokenStorage->getToken()->getUser();

        // Supprimer l'utilisateur de la liste des participants
        $event->removeUserParticipate($user);

        // Enregistrer l'événement
        $entityManager->persist($event);
        $entityManager->flush();

        // Rediriger vers la page de l'événement
        return $this->redirectToRoute('app_events_show', ['id' => $id]);
    }

    // Trier les événements 
    #[Route('/events/sorted', name: 'app_events_sorted')]
    public function eventsSorted(Request $request, EventRepository $eventRepository, OpenLigaDBClient $httpClient): Response
    {
        // Récupération des paramètres de filtrage depuis la requête.
        $teamIdInput = $request->query->get('teamId');
        $statusInput = $request->query->get('status', false); // 'false' indique aucun filtre de statut par défaut.

        // filtrer les inputs
        $teamId = filter_var($teamIdInput, FILTER_VALIDATE_INT);
        $status = filter_var($statusInput, FILTER_VALIDATE_BOOLEAN);

        // Récupère tous les événements de la base de données, triés par date de commencement.
        $events = $eventRepository->findBy([], ['startDate' => 'ASC']);

        // Initialisation du tableau qui va contenir les événements filtrés.
        $eventsSort = [];

        // Boucle sur chaque événement pour déterminer s'il correspond aux critères de filtrage.
        foreach ($events as $event) {
            // Initialisation de la variable pour décider de l'inclusion de l'événement.
            $includeEvent = false;

            // Chargement des données du match de l'API externe si un ID d'équipe est spécifié.
            if ($teamId) {
                $match = $httpClient->getMatchByTeamId($event->getMatchId());
                // Vérification si l'événement correspond à l'équipe spécifiée.
                $isTeamMatch = ($match['team1']['teamId'] == $teamId || $match['team2']['teamId'] == $teamId);
            } else {
                // Si aucun ID d'équipe n'est spécifié, l'événement correspond par défaut à ce critère.
                $isTeamMatch = true;
            }

            // Vérification si l'événement correspond au statut spécifié.
            $isStatusMatch = $status !== false ? $event->isIsLocked() == $status : true;

            // Inclusion de l'événement si et seulement si il correspond aux deux critères.
            if ($isTeamMatch && $isStatusMatch) {
                $eventsSort[] = $event;
            }
        }

        // Affichage de la page des événements
        return $this->render('events/eventSorted.html.twig', [
            'eventsSorted' => $eventsSort,
        ]);
    }
}
