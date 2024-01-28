<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentMatchController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(): Response
    {
        return $this->render('matchs/match.html.twig', [
            'controller_name' => 'EventsController',
        ]);
    }

    // Add commentMatch
    #[Route('/commentMatch/add', name: 'commentMatch_add')]
    public function addCommentMatch(): Response
    {
        return $this->redirectToRoute('match_view', ['id' => $matchId]);
    }
}
