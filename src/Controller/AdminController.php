<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(UserRepository $userManager): Response
    {
        // On recuère tout les utilisateurs
        $users = $userManager->findAll();

        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }

    // Annonimiser un utilisateur
    #[Route('/admin/delete/user/{id}', name: 'admin_delete_user')]
    public function deleteUser($pseudoInput,EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, TokenStorageInterface $tokenStorage, UserRepository $userRepository): Response
    {   
        $pseudo = filter_var($pseudoInput, FILTER_SANITIZE_SPECIAL_CHARS);

        $user = $userRepository->findOneBy(['pseudo' => $pseudo]);
        
        $userSeesion = $tokenStorage->getToken()->getUser();

        //the user is equal to the user in seesion
        if($user == $userSeesion){
            // anonimiser le pseudo
            $user->setPseudo('Profile supprimé');

            
            // Generate a new random password for the user
            $newPassword = bin2hex(random_bytes(8));
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            // Generate a unique email value for the user
            $uniqueValue = 'deleted_user_' . uniqid();
            $user->setEmail($uniqueValue);

            // Set the user as not verified
            $user->setIsVerified(false);

            // Persist the changes to the user entity
            $em->persist($user);
            $em->flush();

            // Clear the token storage to log out the user
            $tokenStorage->setToken(null);
            //let him see his profile
            $this->addFlash('success', 'Your profile was succesfully deleted.');
            return $this->redirectToRoute('app_matchsListe');
        }

         //if the user was different from the current user in seesion send back to home and add flash message
         $this->addFlash('danger', 'You dont have access to this page.');
         return $this->redirectToRoute('/registration/register.html.twig');
 
    }

    //Bannier un utilisateur
    #[Route('/admin/ban/user/{id}', name: 'admin_ban_user')]
    public function banUser($id, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        $user->setIsBanned(true);
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'The user was succesfully banned.');
        return $this->redirectToRoute('app_admin');
    }

    //Débannier un utilisateur
    #[Route('/admin/unban/user/{id}', name: 'admin_unban_user')]
    public function unbanUser($id, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        $user->setIsBanned(false);
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'The user was succesfully unbanned.');
        return $this->redirectToRoute('app_admin');
    }
}
