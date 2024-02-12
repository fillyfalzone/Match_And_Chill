<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\HttpClient\OpenLigaDBClient;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FavoriteMatchRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(TokenStorageInterface $tokenStorage): Response
    {   
        
    
        return $this->render('/profil/profil.html.twig', [
            
        ]);
    }

    /**
     *  Mes matchs favoris
     */

    #[Route('/profil/mymatchs', name: 'app_myMatchs')]
    public function mymatchs(TokenStorageInterface $tokenStorage, FavoriteMatchRepository $favoriteMatchRepo, OpenLigaDBClient $httpClient): Response
    {   
        
        // On récupère l'utilisateur connecté
        $user = $tokenStorage->getToken()->getUser();
        $userId = $user->getId();
        

        // On récupère les matchs favoris de l'utilisateur
        $favoriteMatchsId = $favoriteMatchRepo->findBy(['userId' => $userId]);

        // On récupère les matchs depuis l'API
        $favoriteMatchs = [];
        foreach ($favoriteMatchsId as $favoriteMatch) {
            $favoriteMatchs[] = $httpClient->getMatchById($favoriteMatch->getMatchId());
        }
       
        return $this->render('/profil/mymatchs.html.twig', [
            'favoriteMatchs' => $favoriteMatchs,
        ]);
    }

    #[Route('/profil/mymatchs/data', name: 'app_myMatchs_data')]
    public function mymatchsJson(TokenStorageInterface $tokenStorage, FavoriteMatchRepository $favoriteMatchRepo, OpenLigaDBClient $httpClient): JsonResponse
    {   
        // On récupère l'utilisateur connecté
        $user = $tokenStorage->getToken()->getUser();
        $userId = $user->getId();
        
        // On récupère les matchs favoris de l'utilisateur
        $favoriteMatchsId = $favoriteMatchRepo->findBy(['userId' => $userId]);

        // On récupère les matchs depuis l'API
        $favoriteMatchs = [];
        foreach ($favoriteMatchsId as $favoriteMatch) {
            $favoriteMatchs[] = $httpClient->getMatchById($favoriteMatch->getMatchId());
        }

        // On retourne les matchs favoris au format JSON
        return new JsonResponse($favoriteMatchs);
    }

    /**
     * Mes évènement favoris 
    */

    #[Route('/profil/myevents', name: 'app_myEvents')]
    public function myEvent(TokenStorageInterface $tokenStorage, EventRepository $eventManager): Response
    {   //On recupère le user en ligne 
        $user = $tokenStorage->getToken()->getUser();

        $events = $user->getEvents();
        
        return $this->render('/profil/myEvents.html.twig', [
            'events' => $events,
        ]);
    }

    /**     
        * Mon Profil utilisateur 
     */
    // Annonimiser le profil utilisateur
     #[Route('/delete/{pseudoInput}', name: 'delete_profile')]
    public function deleteUser($pseudoInput,EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, TokenStorageInterface $tokenStorage, UserRepository $userRepository): Response
    {   
        $pseudo = filter_var($pseudoInput, FILTER_SANITIZE_SPECIAL_CHARS);

        $user = $userRepository->findOneBy(['pseudo' => $pseudo]);
        
        $userSeesion = $tokenStorage->getToken()->getUser();

        //the user is equal to the user in seesion
        if($user == $userSeesion){
            // anonimiser le pseudo
            $user->setPseudo('Profile supprimé');

            
            // Generate a new random password for the user
            $newPassword = bin2hex(random_bytes(8));
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            // Generate a unique email value for the user
            $uniqueValue = 'deleted_user_' . uniqid();
            $user->setEmail($uniqueValue);

            // Set the user as not verified
            $user->setIsVerified(false);

            // set the user as banned
            $user->setIsBanned(true);

            // Persist the changes to the user entity
            $em->persist($user);
            $em->flush();

            // Clear the token storage to log out the user
            $tokenStorage->setToken(null);
            //let him see his profile
            $this->addFlash('success', 'Your profile was succesfully deleted.');
            return $this->redirectToRoute('app_matchsListe');
        }

         //if the user was different from the current user in seesion send back to home and add flash message
         $this->addFlash('danger', 'You dont have access to this page.');
         return $this->redirectToRoute('/registration/register.html.twig');
 
    }

   
}
