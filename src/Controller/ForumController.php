<?php

namespace App\Controller;

use App\Entity\CategoryForum;
use App\Form\CategoryForumType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CategoryForumRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ForumController extends AbstractController
{
    #[Route('/forum', name: 'app_forum')]
    public function index(): Response
    {
        return $this->render('forum/index.html.twig', [
            'controller_name' => 'ForumController',
        ]);
    }

    /*
        * CRUD CATEGORIES
    */

    /* Craeté and edit category*/

    /* Read categoreis */
    #[Route('/forum/categoriesForum', name: 'read_categoriesForum')]
    public function read( CategoryForumRepository $categoryForumRepository): Response
    {
        //Call all categories from bdd
        $categories = $categoryForumRepository->findBy([], ['id' => 'ASC']);

        return $this->render('forum/categoriesForum.html.twig', [
            'categories' => $categories,
        ]);
    }

    /* Delete category */

    #[Route('/forum/categoriesForum/{id}/delete', name: 'delete_categoryForum')]
    public function delete(CategoryForum $categoryForum, EntityManagerInterface $entityManager) : Response 
    {
        /*
            * Supprime une catégorie de forum en utilisant l'entityManager.
        */
        $entityManager->remove($categoryForum);
        $entityManager->flush();

        return $this->redirectToRoute('read_categoriesForum');
    }

   /**
        * Ce code gère la création et la modification des catégories de forum.
    */

    #[Route('/forum/categoriesForum/{id}/edit', name: 'edit_categoryForum')]
    #[Route('/forum/categoriesForum/new', name: 'new_categoryForum')]
    public function new_edit(CategoryForum $categoryForum = null, Request $request, EntityManagerInterface $entityManager): Response
    {   
        // S'il n'existe pas de catégorie créé une nouvelle
        if (!$categoryForum) {
            $categoryForum = new CategoryForum();
        }
        //Créer un formulaire à partir de CategoryForumType et de l'entité CategoryForum
        $form = $this->createForm(CategoryForumType::class, $categoryForum);
        //Soumettre les données du formulaire
        $form->submit($request->request->all());
        
        // Lorsqu'un formulaire est soumis et valide, la catégorie de forum est persistée en base de données.
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categoryForum);
            $entityManager->flush();

            // l'utilisateur est redirigé vers la page de lecture des catégories de forum.
            return $this->redirectToRoute('read_categoriesForum');
        }
        
        return $this->render('forum/new_edit.html.twig', [
            'form' => $form->createView(),
            'categoryForumId' => $categoryForum->getId()
        ]);
}
    // #[Route('/forum/categoriesForum/{id}/edit', name: 'edit_category')]
    // #[Route('/forum/categoriesForum/new', name: 'new_category')]
    // public function new_edit(CategoryForum $categoryForum = null, Request $Request, EntityManagerInterface $entityManager) : Response
    // {
    //     if (!$categoryForum) {
    //         $categoryForum = new CategoryForum();
    //     }

    //     $form = $this->createForm(CategoryForumType::class, $categoryForum);

    //     $form->handleRequest($Request);
        
    //     if ($form->isSubmitted() && form->isValid()) {

    //         $categoryForum = $form->getData();

    //         $entityManager->persist($categoryForum);

    //         $entityManager->flush();

    //         return $this->redirectToRoute('read_categoriesForum');
    //     }

    //     return $this->render('categoriesForum/new_edit.html.twig', [
    //         'form' => $form,
    //         'categoryForumId' => $categoryForum->getId()
    //     ]);
    // }

}
