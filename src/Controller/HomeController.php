<?php

namespace App\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        $dateNow = new DateTime();
        $date = $dateNow->format('Y-m-d');

        return $this->render('home/index.html.twig', [
            'date' => $date,
        ]);
    }
}
