<?php

namespace App\Controller;

use App\Entity\CommentMatch;
use App\HttpClient\OpenLigaDBClient;
use Doctrine\ORM\EntityManagerInterface;
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

    /*
        * Recupérer un match par id  
    */
    #[Route('/matchsList/match/{matchID}', name: 'app_match')]
    public function detailsMatch($matchID, OpenLigaDBClient $httpClient): Response
    {   
        // recupérer le match via httpClient 
        $match = $httpClient->getMatchById($matchID);

        // déterminer le statut du match
        $status = "";
        $currentDateTime = new \DateTime();

        // Comparer la date actuelle avec la date du match pour déterminer le statut
        $matchDate = new \DateTime($match['matchDateTime']);

        if ($match['matchIsFinished']) {
            $status = "Terminé";
        } elseif ($currentDateTime < $matchDate) {
            $status = "Prévu";
        } elseif ($currentDateTime >= $matchDate && !$match['matchIsFinished']) {
            $status = "En cours";
        }

        return $this->render('matchs/match.html.twig', [
            'matchID' => $matchID,
            'match' => $match,
            'status' => $status,
        ]);
    }
    

    // /*
    //     * CRUD commentaire de match
    // */

    // Delete comment
    #[Route('/matchsList/match/{matchID}/delete/comment/{id}/', name: 'app_match_delete_comment')]
    public function deleteComment(CommentMatch $comment, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('app_match');
    }

    //add and edit comment 


}