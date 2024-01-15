<?php

namespace App\Controller;

use App\HttpClient\OpenLigaDBClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FootDataController extends AbstractController
{
    #[Route('/foot/data', name: 'app_foot_data')]
    public function index(OpenLigaDBClient $httpClient): Response
    {
        $leagues = $httpClient->getLeagues();
       
        return $this->render('foot_data/index.html.twig', [
            'leagues' => $leagues,  
        ]);
    }

}
