<?php

namespace App\Controller;

use App\Entity\CommentMatch;
use App\Form\CommentMatchFormType;
use App\HttpClient\OpenLigaDBClient;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommentMatchRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
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
    #[Route('/matchsList/match/{matchID}/edit/comment/{id}', name: 'app_match_edit_comment')]
    public function detailsMatch($matchID, OpenLigaDBClient $httpClient, CommentMatchRepository $commentManager, CommentMatch $comment=null,  EntityManagerInterface $entityManager, Request $request, UserInterface $user): Response
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

        // Créer un commentaire 
        // On verifi si le commentaire existe sinon on crée un nouveau
        if (!$comment) {
            $comment = new CommentMatch();
        }
    
        // Créer le formulaire avec l'entité CommentMatch
        $form = $this->createForm(CommentMatchFormType::class, $comment);
    
        // Gérer la requête HTTP
        $form->handleRequest($request);

        //On verifi si le user est connecté, si oui on recupère sont id 
        if ($user instanceof \App\Entity\User) {
            $userId = $user->getId();
        }

    
        // Traiter le formulaire si soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérez les données du formulaire
            $comment = $form->getData();
            // si il n'y a pas l'id de l'utilisate ajout l'id dz celui qui est connecté
            if (!$comment->getUserID()) {
                $comment->setUserID($userId);
            }
            $comment->setMatchID($matchID);
    
            // Enregistrer le commentaire en base de données
            $entityManager->persist($comment);
            $entityManager->flush();
    
            // Rediriger vers la liste des matches
            return $this->redirectToRoute('app_match');
        }
          // Tout les commentaires dont le match à pour id $matchID
          $comments = $commentManager->findBy(['matchID' => $matchID], ['creationDate' => 'ASC']);

        return $this->render('matchs/match.html.twig', [
            'matchID' => $matchID,
            'match' => $match,
            'comments' => $comments,
            'status' => $status,
            'form' => $form->createView(),
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

    // #[Route('/matchsList/match/{matchID}/edit/comment/{id}', name: 'app_match_edit_comment')]
    // public function new_edit (CommentMatch $comment, Request $request, EntityManagerInterface $entityManager): Response
    // {
    //     // On verifi si le commentaire existe sinon on crée un nouveau
    //     if (!$comment) {
    //         $comment = new CommentMatch();
    //     }
    
    //     // Créer le formulaire avec l'entité CommentMatch
    //     $form = $this->createForm(CommentMatchFormType::class, $comment);
    
    //     // Gérer la requête HTTP
    //     $form->handleRequest($request);
    
    //     // Traiter le formulaire si soumis et valide
    //     if ($form->isSubmitted() && $form->isValid()) {
    //         // Récupérez les données du formulaire
    //         $comment = $form->getData();
    
    //         // Enregistrer le commentaire en base de données
    //         $entityManager->persist($comment);
    //         $entityManager->flush();
    
    //         // Rediriger vers la liste des matches
    //         return $this->redirectToRoute('app_match');
    //     }
    
    //     // Rendre la vue avec le formulaire
    //     return $this->render('matchs/match.html.twig', [
    //         // Ajoutez le formulaire à la vue
    //         'form' => $form->createView(),
    //         'commentId' => $comment->getId(),
    //     ]);
    // }

}