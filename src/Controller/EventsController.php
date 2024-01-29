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
        // On verifie le champ anti-bot
        if (!empty($_POST['honeyPot'])) {
            throw new \Exception('Vous êtes un robot');
        } else {
            
            //On récupère les données du formulaire
            if( isset($_POST['name']) 
                && isset($_POST['category'])
                && isset($_POST['description'])
                && isset($_POST['matchId'])
                && isset($_POST['startDate'])
                && isset($_POST['startTime'])
                && isset($_POST['endDate'])
                && isset($_POST['endTime'])
                && isset($_POST['numberOfPlaces'])
                && isset($_POST['adress'])
                && isset($_POST['city'])
                && isset($_POST['zipCode'])
                && isset($_POST['country'])
                && isset($_POST['honeyPot']) )
                {
                    // On filtre les données
                    $name = filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS);
                    $category = filter_var($_POST['category'], FILTER_VALIDATE_INT);
                    $description = filter_var($_POST['description'], FILTER_SANITIZE_SPECIAL_CHARS);
                    $matchId = filter_var($_POST['matchId'], FILTER_VALIDATE_INT);
                    $numberOfPlaces = filter_var($_POST['numberOfPlaces'], FILTER_VALIDATE_INT);
                    $adress = filter_var($_POST['adress'], FILTER_SANITIZE_SPECIAL_CHARS);
                    $city = filter_var($_POST['city'], FILTER_SANITIZE_SPECIAL_CHARS);
                    $zipCode = filter_var($_POST['zipCode'], FILTER_VALIDATE_INT);
                    $country = filter_var($_POST['country'], FILTER_SANITIZE_SPECIAL_CHARS);
                    
    
    
                    // On vérifie que les dates et heures sont valides
                    if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $_POST['startTime']) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $_POST['endTime']))
                    {
                        $startTime = $_POST['startTime'];
                        $endTime = $_POST['endTime'];
    
                    } else {
                        throw new \Exception('L\'heure n\'est pas valide');
                    }
    
                    // On vérifie que les dates et heures sont valides
    
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['startDate']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['endDate'])) {
                        
                        $startDate = $_POST['startDate'];
                        $endDate = $_POST['endDate'];
    
                    } else {
                        throw new \Exception('La date n\'est pas valide');
                    }
    
                    // On vérifie que les champs ne sont pas vides
                    if ($name && $category && $description && $matchId && $startDate && $startTime && $endDate && $endTime && $numberOfPlaces && $adress && $city && $zipCode && $country) 
                    {
                        // On concatène les dates et heures
                        $startDateTime = $startDate . ' ' . $startTime;
                        $endDateTime = $endDate . ' ' . $endTime;

                        // On definie la date de création
                        $creationDate = new \DateTime();
    
                        // On recupère l'utilisateur connecté
                        $user = $tokenStorage->getToken()->getUser();

                        // On recupère la catégorie de l'évènement
                        $category = $categoryEvent->findOneBy(['id' => $category]);

                        // On crée un nouvel évènement
                        $event = new Event();

                        // On hydrate l'objet
                        $event->setName($name);
                        $event->setCategory($category);
                        $event->setDescription($description);
                        $event->setMatchId($matchId);
                        $event->setStartDate(new \DateTime($startDateTime));
                        $event->setEndDate(new \DateTime($endDateTime));
                        $event->setNumberOfPlaces($numberOfPlaces);
                        $event->setAdress($adress);
                        $event->setCity($city);
                        $event->setZipCode($zipCode);
                        $event->setCountry($country);
                        $event->setCreationDate($creationDate);
                        $event->setUser($user);

                        // On enregistre l'évènement
                        $entityManager->persist($event);
                        $entityManager->flush();

                        // On redirige vers la page de l'évènement
                        return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
                    }
                }
            return $this->render('events/new.html.twig', [
                'controller_name' => 'EventsController',
            ]);
        }

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
