<?php

namespace App\Controller;


use DateTimeImmutable;
use App\Form\VerifyEmailFormType;
use Symfony\Component\Mime\Email;
use App\Entity\PasswordResetToken;
use App\Form\PasswordResetFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PasswordResetTokenRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_matchsList');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    //Password reset
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }



    //Email verification
    #[Route(path: '/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, MailerInterface $mailer, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $tokenManager): Response
    {
        // On recupère le formulaire
        $form = $this->createForm(VerifyEmailFormType::class);
        $form->handleRequest($request);

       

        // On vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les données du formulaire
            $email = $form->get('email')->getData();
            
            // On récupère l'utilisateur
            $user = $userRepository->findOneBy(['email' => $email]);
           
            // On vérifie si l'utilisateur existe
            if (!$user) {
                //add flash message d'erreur
                $message = $this->addFlash('error', 'Adresse email inconnue');
                return $this->redirectToRoute('app_verify_email', ['message' => $message]);
            } else {
                // générer le token
                $token = $tokenManager->getToken('any_id')->getValue();

              
                // On crée le token
                $passwordReset = new PasswordResetToken();
                // On enregistre le token en BDD
                $passwordReset->setUser($user);
                $passwordReset->setToken($token);
                $passwordReset->setExpiresAt(new DateTimeImmutable('+ 3 hour'));
                $entityManager->persist($passwordReset);
                $entityManager->flush();

                $formPasswordReset = $this->createForm(PasswordResetFormType::class);
                // Générer l'URL de réinitialisation
                $url = $this->generateUrl('app_password_reset', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                // Envoyer l'email
                $message = (new Email())
                ->from('admin@matchandchill.com')
                ->to($user->getEmail())
                ->subject('Réinitialisation de votre mot de passe')
                ->html(
                    $this->renderView(
                        // chemin vers votre template Twig
                        'security/resetPassword.html.twig',
                        // vous pouvez passer des variables au template ici
                        [
                            'url' => $url,
                            
                        ]
                    )
                );
            // On envoie l'email
            $mailer->send($message);
            

                // message flash de succès
                $message = $this->addFlash('success', 'Un email de réinitialisation de mot de passe vous a été envoyé.');

                return $this->redirectToRoute('app_verify_email_sent', ['message' => $message]);
            }

        }

        return $this->render('security/verifyEmail.html.twig', [
            'verifyEmailForm' => $form->createView(),
        ]);
    }



    //Email verification sent
    #[Route(path: '/verify/email/sent', name: 'app_verify_email_sent')]
    public function verifyEmailSent(): Response
    {
        
        return $this->render('security/verifyEmailSent.html.twig', [
            
        ]);
    }


    //reset password
    #[Route(path: '/reset/password/{token}', name: 'app_password_reset')]
    public function resetPassword( EntityManagerInterface $entityManager, PasswordResetTokenRepository $passwordResetToken, Request$request, UserPasswordHasherInterface $userPasswordHasher, string $token): Response
    {
        // on recupère le formulaire
        $form = $this->createForm(PasswordResetToken::class);
        $form->handleRequest($request);

        // on vérifie si le formulaire est soumis et valid
        if ($form->isSubmitted() && $form->isValid()) {
            // on récupère les données du formulaire
            $password = $form->get('plainPassword ')->getData();

            // on récupère le password reset token
            $passwordReset = $passwordResetToken->findOneBy(['token' => $token]);

            // on vérifie si le token existe
            if (null === $passwordReset) {
                // add flash message d'erreur
                $message = $this->addFlash('error', 'Utilisateur inconnu');
                return $this->redirectToRoute('app_password_reset', ['message' => $message]);
            } else {
                // on récupère verifi la date d'expiration du token
                $now = new DateTimeImmutable();
                if ($now > $passwordReset->getExpiresAt()) {
                    // add flash message d'erreur
                    $message = $this->addFlash('error', 'Le mot de passe a expiré');
                    return $this->redirectToRoute('app_password_reset', ['message' => $message]);
                } else {
                    // on récupère l'utilisateur
                    $user = $passwordReset->getUser();

                    // on vérifie si l'utilisateur existe
                    if (null === $user) {
                        // add flash message d'erreur
                        $message = $this->addFlash('error', 'Utilisateur inconnu');
                        return $this->redirectToRoute('app_password_reset', ['message' => $message]);
                    } else {
                        // on met à jour le mot de passe haché de l'utilisateur
                        $user->setPassword(
                            $userPasswordHasher->hashPassword(
                                $user,
                                $password
                            )
                        );
                       
                        // on enregistre l'utilisateur en base de données
                        $entityManager->persist($user);
                        //on supprime le token
                        $entityManager->remove($passwordReset);
                        $entityManager->flush();
                        
                        // add flash message de succès
                        $message = $this->addFlash('success', 'Votre mot de passe a été mis à jour');
                        return $this->redirectToRoute('app_login', ['message' => $message]);
                    }
                }                       
               
            }
        } 
        
        return $this->render('security/resetPassword.html.twig', [
            'resetPasswordForm' => $form->createView(),
        ]);

    }

}
