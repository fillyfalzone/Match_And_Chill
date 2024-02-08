<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentEventController extends AbstractController
{
    #[Route('/comment/event', name: 'app_comment_event')]
    public function index(): Response
    {
        return $this->render('comment_event/index.html.twig', [
            'controller_name' => 'CommentEventController',
        ]);
    }
}
