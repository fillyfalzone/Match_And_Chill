<?php

namespace App\Controller;

use Exception;
use App\Entity\CommentMatch;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommentMatchRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
    #[Route('matchsList/match/{matchId}/add', name: 'commentMatch_add')]
    public function addCommentMatch($matchId, TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager, CommentMatch $commentMatch = null, Request $Request): Response
    {
        // recupète le user connecté
        $user = $tokenStorage->getToken()->getUser();

        // recupère le commentaire envoyé
        $comment = $Request->request->get('comment-match');
        $creationDate = new \DateTime();

        // on crée un nouveau commentaire
        $commentMatch = new CommentMatch();

        // on hydrate le commentaire
        $commentMatch->setText($comment);
        $commentMatch->setUser($user);
        $commentMatch->setMatchId($matchId);
        $commentMatch->setCreationDate($creationDate);

        // on sauvegarde le commentaire
        $entityManager->persist($commentMatch);
        $entityManager->flush();

        return $this->redirectToRoute('match_view', ['id' => $matchId]);
    }

    // edit commentMatch
    #[Route('matchsList/match/{matchId}/edit/{commentId}', name: 'commentMatch_edit')]
    public function editCommentMatch($matchId, $commentId, EntityManagerInterface $entityManager, CommentMatchRepository $commentMatch, Request $Request): JsonResponse
    {
        // recupère le commentaire envoyé
        $data = json_decode($Request->getContent(), true);
        $commentText = $data['commentText'] ?? null;

        $commentMatch = $commentMatch->find($commentId);

        // on hydrate le commentaire
        $commentMatch->setText($commentText);
        
        
        // on sauvegarde le commentaire
        $entityManager->persist($commentMatch);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Commentaire mis à jour']);
    }

    // delete commentMatch
    #[Route('matchsList/match/{matchId}/delete/{id}', name: 'commentMatch_delete')]
    public function deleteCommentMatch($matchId, $id, EntityManagerInterface $entityManager, CommentMatchRepository $commentMatch, Request $request): Response
    {

        $commentMatch = $commentMatch->find($id);

        if (!$commentMatch) {
            // Gérer l'erreur, par exemple, renvoyer une réponse ou une exception
            throw new Exception("Commentaire introuvable");
        }
    
        // Vérifier le jeton CSRF
        $csrfToken = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $commentMatch->getId(), $csrfToken)) {
            // on supprime le commentaire
            $entityManager->remove($commentMatch);
            $entityManager->flush(); 

            return $this->redirectToRoute('match_view', ['matchId' => $matchId, 'id' => $id]);
        }
    
        // Gérer l'échec de la validation CSRF
        throw new Exception(" token CSRF invalide");
    }
}
