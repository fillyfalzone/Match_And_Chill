<?php

namespace App\Controller;

use App\HttpClient\OpenLigaDBClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MatchsController extends AbstractController
{
    #[Route('/matchsList', name: 'app_matchsList')]
        public function index(): Response
    {
        
        
        return $this->render('matchs/matchsList.html.twig', [
            'controller_name' => 'MatchsController',
        ]);
    }


    #[Route('/matchsList/match/{matchID}', name: 'app_match')]
        public function detailsMatch($matchID, OpenLigaDBClient $httpClient): Response
    {
        $match = $httpClient->getMatchById($matchID);
        
        return $this->render('matchs/match.html.twig', [
            'matchID' => $matchID,
            'match' => $match,
        ]);
    }

}