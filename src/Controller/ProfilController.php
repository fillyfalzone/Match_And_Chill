<?php

namespace App\Controller;

use App\HttpClient\OpenLigaDBClient;
use App\Repository\FavoriteMatchRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(TokenStorageInterface $tokenStorage): Response
    {   
        $user = $tokenStorage->getToken()->getUser();
    
        return $this->render('profil/myAccount.html.twig', [
            'user' => $user,
        ]);
    }


    #[Route('/mymatchs', name: 'app_myMatchs')]
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
       
        return $this->render('profil/mymatchs.html.twig', [
            'favoriteMatchs' => $favoriteMatchs,
        ]);
    }

    #[Route('/mymatchs/data', name: 'app_myMatchs_data')]
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

    
}
