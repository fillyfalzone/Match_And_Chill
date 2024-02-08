<?php

namespace App\Controller;

use Exception;
use App\Entity\CommentMatch;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommentMatchRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
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
    public function addCommentMatch(TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager, CommentMatch $commentMatch = null, Request $request, CsrfTokenManagerInterface $tokenManager, int $matchId): Response
    {   

        // recupère le commentaire envoyé
        $comment = $request->request->get('comment-match');

        //recupère le token
        $csrfTokenValue = $request->request->get('token-comment-match');
        $csrfToken = new CsrfToken('token-delete-comment', $csrfTokenValue);

        if (!$tokenManager->isTokenValid($csrfToken)) {
            // Gérer l'échec de la vérification du token CSRF, par exemple en renvoyant une erreur
            throw new \Exception('Invalid CSRF token');
        }

        // recupète le user connecté
        $user = $tokenStorage->getToken()->getUser();

        // date de création
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
    public function editCommentMatch( EntityManagerInterface $entityManager, CommentMatchRepository $commentMatch, Request $request, CsrfTokenManagerInterface $tokenManager,int $matchId, int $commentId): JsonResponse
    {
        // recupère le commentaire envoyé
        $data = json_decode($request->getContent(), true);
        $commentText = $data['commentText'] ?? null;
        $csrfTokenValue = $data['tokenEdit'] ?? null;

        $csrfToken = new CsrfToken('token-edit-comment', $csrfTokenValue);

        if (!$tokenManager->isTokenValid($csrfToken)) {
            // Gérer l'échec de la vérification du token CSRF, par exemple en renvoyant une erreur
            throw new \Exception('Invalid CSRF token');
        }

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
    public function deleteCommentMatch( EntityManagerInterface $entityManager, CommentMatchRepository $commentMatch, Request $request,  CsrfTokenManagerInterface $tokenManager, int $matchId, int $id): Response
    {

        //recupère le token
        $csrfTokenValue = $request->request->get('token-delete-comment');
        $csrfToken = new CsrfToken('token-delete-comment', $csrfTokenValue);

        if (!$tokenManager->isTokenValid($csrfToken)) {
            // Gérer l'échec de la vérification du token CSRF, par exemple en renvoyant une erreur
            throw new \Exception('Invalid CSRF token');
        }

        // recupère le commentaire
        $commentMatch = $commentMatch->find($id);

        if (!$commentMatch) {
            // Gérer l'erreur
            throw new Exception("Commentaire introuvable");
        }
        // on supprime le commentaire
        $entityManager->remove($commentMatch);
        $entityManager->flush(); 

        return $this->redirectToRoute('match_view', ['matchId' => $matchId, 'id' => $id]);
 
    }
}
