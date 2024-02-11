<?php

namespace App\Controller;

use Exception;
use App\Entity\CommentMatch;
use App\Entity\FavoriteMatch;
use App\HttpClient\OpenLigaDBClient;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommentMatchRepository;
use App\Repository\FavoriteMatchRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;



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
    #[Route('/matchsList/match/{matchId}', name: 'app_match')]
    public function detailsMatch($matchId, OpenLigaDBClient $httpClient, CommentMatchRepository $commentMatch, CsrfTokenManagerInterface $csrfTokenManager): Response
    {   

        //les tokens de sécurité
        $tokenEdit = $csrfTokenManager->getToken('token-edit-comment');
        $tokenDelete = $csrfTokenManager->getToken('token-delete-comment');
        $tokenComment = $csrfTokenManager->getToken('token-comment-match');

        // recupérer le match via httpClient 
        $match = $httpClient->getMatchById($matchId);

        //recupérer le classement de la ligue
        $table = $httpClient->getTable();

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

        /**
            * Charger les commentaires du match 
        */
         // recupère les commentaires du match
         $comments = $commentMatch->findBy(['matchId' => $matchId], ['creationDate' => 'ASC']);
         
        return $this->render('matchs/match.html.twig', [
            'comments' => $comments,
            'matchId' => $matchId,
            'match' => $match,
            'status' => $status,
            'table' => $table,
            'tokenEdit' => $tokenEdit,
            'tokenDelete' => $tokenDelete,
            'tokenComment' => $tokenComment,
        ]);
    }
  
    /*
        *  Gestion des matchs favoris 
    */
    // Ajouter ou supprimer un match des favoris
    // access control 
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour ajouter un match à vos favoris')]
    #[Route('/matchList/favorite', name: 'update_favorite_match', methods: ['POST'])]
    public function favoriteMatch(Request $request, TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager, FavoriteMatchRepository $favoriteManager): Response
    {
        // Récupération de l'utilisateur connecté à partir du token de sécurité
        $user = $tokenStorage->getToken()?->getUser();
        // Vérifier si un utilisateur est connecté
        if (!$user) {
            // Si aucun utilisateur n'est connecté, renvoyer une réponse JSON avec un message d'erreur
            // return new JsonResponse(['status' => 'error', 'message' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
            throw new Exception("Utilisateur non connecté");
        }
        // Récupération de l'ID de l'utilisateur
        $userId = $user->getId();

        // Décodage des données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);
        // néttoyage des données.
        $matchId = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT); // ID du match à mettre en favori ou à retirer des favoris
        $status = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN); // Status indicating whether the match should be added or removed from favorites
       
        try {
            // Traitement si le match doit être ajouté aux favoris
            if ($status) {

                // Vérification de l'existence de l'entrée pour éviter les doublons
                $existingFavorite = $favoriteManager->findOneBy(['matchId' => $matchId, 'userId' => $userId]);
                if (!$existingFavorite) {
                    // Création d'une nouvelle entrée FavoriteMatch si elle n'existe pas déjà
                    $favoriteMatch = new FavoriteMatch();
                    $favoriteMatch->setMatchID($matchId);
                    $favoriteMatch->setUserID($userId);

                    // Persister la nouvelle entrée dans la base de données
                    $entityManager->persist($favoriteMatch);
                }
            } else {
                // Traitement si le match doit être retiré des favoris
                $favoriteMatch = $favoriteManager->findOneBy(['matchId' => $matchId, 'userId' => $userId]);
                if ($favoriteMatch) {
                    // Suppression de l'entrée existante si elle existe
                    $entityManager->remove($favoriteMatch);
                }
            }
            // Appliquer les changements dans la base de données
            $entityManager->flush();
        } catch (\Exception $e) {
            // Gestion des exceptions, par exemple, enregistrer un message d'erreur
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur du serveur', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Renvoyer une réponse JSON indiquant que l'action a été exécutée avec succès
        return new JsonResponse(['status' => 'success']);
    }

    // Recupérer les matchs favoris pour les envoyer au chargement de la page
    // #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour ajouter un match à vos favoris')]
    #[Route('/matchList/favorite/getmatchs', name: 'get_favorite_matchs')]
    public function getFavoriteMatchs (TokenStorageInterface $tokenStorage, FavoriteMatchRepository $favoriteMatchRepository, EntityManagerInterface $entityManager): Response 
    {
        // Récupération de l'utilisateur connecté à partir du token de sécurité
        $user = $tokenStorage->getToken()?->getUser();

        if (!$user) {
            // return new JsonResponse(['status' => 'error', 'message' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
            throw new Exception("Utilisateur non connecté");
        }

        $userId = $user->getId();

        $favorites = $favoriteMatchRepository->findBy(['userId' => $userId]);

        $favoriteMatchIds = array_map(fn($fav) => $fav->getMatchID(), $favorites);

        return new JsonResponse(['favoriteMatchIds' => $favoriteMatchIds]);
    }



    /*
        * CRUD commentaire de match
    */

    // Add comment 
    // #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour ajouter un match à vos favoris')]
    #[Route('/commentMatch/add', name: 'comment_match_add')]
    public function addCommentMatch(CommentMatch $comment, EntityManagerInterface $entityManager): Response
    {
        
        return $this->redirectToRoute('app_match');
    }


    // Delete comment
    // #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour ajouter un match à vos favoris')]
    #[Route('/matchsList/match/{matchId}/delete/comment/{id}', name: 'app_match_delete_comment')]
    public function deleteComment(CommentMatch $comment, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('app_match');
    }

    //add and edit comment 


}