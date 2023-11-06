<?php

namespace App\Controller;

use App\Entity\CategoryForum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\BrowserKit\Request;
use App\Repository\CategoryForumRepository;
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
    * CATEGORY CRUD
    */
   
    #[Route('/forum/category', name: 'app_category')]
    public function category(CategoryForumRepository $categoryForumRepository): Response
    {
        $categoriesForum = $categoryForumRepository->findAll();

        return $this->render('forum/category.html.twig', [
            'categoriesForum' => $categoriesForum,
        ]);
    }
    
     // Create a new category

    #[Route('/forum/category/new', name: 'app_new')]
    #[Route('/forum/category/new', name: 'app_edit')]
    public function new_edit(CategoryForum $categoryForum = null, Request $Resquest, EntityManagerInterface $entityManager): Response
    {

        
        return $this->render('forum/new_edit.html.twig', [
            'controller_name' => 'ForumController',
        ]);
    }
}
