<?php

namespace App\Controller;

use App\Entity\CommentMatch;
use App\Entity\FavoriteMatch;
use App\Entity\User;
use App\HttpClient\OpenLigaDBClient;
use App\Repository\FavoriteMatchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    
    /*
        *  Gestion des matchs favoris 
    */
    #[Route('/matchList/favorite/{matchID}', name: 'handle_favorite_match')]
    public function favoriteMatch($matchID, Request $request, EntityManagerInterface $entityManager, FavoriteMatch $favoriteMatch, FavoriteMatchRepository $favoriteManager)
    {
        $favorite = $request->request->get('favorite-match');



        if ( $favorite === true) {

            $favoriteMatch = new FavoriteMatch();
            $favoriteMatch->setMatchID($matchID);
            // $favoriteMatch->setUserID($user->getId());

            $entityManager->persist($favoriteMatch);
          
            $entityManager->flush(); // Un seul flush après toutes les insertions
        } else if ($favorite === false) {
            $favoriteMatch = $favoriteManager->findOneBy(['matchID' => $matchID]);

            $favoriteManager->remove($favoriteMatch);
            $favoriteManager->flush();
        }
        return new JsonResponse(['status' => 'success']);
    }

    /*
        * CRUD commentaire de match
    */

    // Delete comment
    #[Route('/matchsList/match/{matchID}/delete/comment/{id}', name: 'app_match_delete_comment')]
    public function deleteComment(CommentMatch $comment, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('app_match');
    }

    //add and edit comment 


}