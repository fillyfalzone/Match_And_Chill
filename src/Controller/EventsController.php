<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CategoryEventRepository;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventsController extends AbstractController
{
    #[Route('/events', name: 'app_events')]
    public function index(): Response
    {   
        return $this->render('events/events.html.twig', [
            'controller_name' => 'EventsController',
        ]);
    }

    // New event
    #[Route('/events/new', name: 'app_events_new')]
    public function new(Event $event = null, CategoryEventRepository $categoryEvent, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, Request $request ): Response
    {   
        // Création d'un nouvel objet Event
        $event = new Event();

        // Création du formulaire
        $form = $this->createForm(EventFormType::class, $event);

        // Traitement de la requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          
            // // on verifie le honeypot
            // if (!empty($form->get('honeyPot')->getData())) {
            //     throw $this->createNotFoundException();
            // }
            // Définir l'utilisateur et la date de création
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
        return $this->render('events/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    

    // Edit event
    #[Route('/events/edit/{id}', name: 'app_events_edit')]
    public function edit($id, EntityManagerInterface $entityManager,  EventRepository $eventRepository, Event $event,  Request $request): Response
    {   
        // On recupère le formulaire 
        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);

        // On vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On enregistre l'événement
            $entityManager->persist($event);
            $entityManager->flush();

            // On redirige vers la page de l'événement
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        return $this->render('events/edit.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
        ]);
    }

    // Delete event
    #[Route('/events/delete/{id}', name: 'app_events_delete')]
    public function delete($id, EntityManagerInterface $entityManager, EventRepository $eventRepository): Response
    {
        // Récupérer l'événement
        $event = $eventRepository->find($id);

        // Supprimer l'événement
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->redirectToRoute('app_events');
    }

    // show event
    #[Route('/events/show/{id}', name: 'app_events_show')]
    public function show($id, EventRepository $eventRepository, TokenStorageInterface $tokenStorage ): Response
    {   
        // Récupérer l'événement
        $event = $eventRepository->find($id);

        // On recupère l'utilisateur connecté
        $user = $tokenStorage->getToken()->getUser();

        return $this->render('events/show.html.twig', [
            'event' => $event,
            'user' => $user,
        ]);
    }
}
