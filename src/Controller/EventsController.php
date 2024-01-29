<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\CategoryEventRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    public function new(Event $event = null, CategoryEventRepository $categoryEvent, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage ): Response
    {   
        
        return $this->render('events/new.html.twig', [
            'controller_name' => 'EventsController',
        ]);
    }

    // Edit event
    #[Route('/events/edit/{id}', name: 'app_events_edit')]
    public function edit($id, CategoryEventRepository $categoryEvent, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage ): Response
    {
        return $this->render('events/edit.html.twig', [
            'controller_name' => 'EventsController',
        ]);
    }

    // Delete event
    #[Route('/events/delete/{id}', name: 'app_events_delete')]
    public function delete($id, CategoryEventRepository $categoryEvent, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage ): Response
    {
        return $this->render('events/delete.html.twig', [
            'controller_name' => 'EventsController',
        ]);
    }

    // show event
    #[Route('/events/show/{id}', name: 'app_events_show')]
    public function show($id, CategoryEventRepository $categoryEvent, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage ): Response
    {
        return $this->render('events/show.html.twig', [
            'controller_name' => 'EventsController',
        ]);
    }
}
