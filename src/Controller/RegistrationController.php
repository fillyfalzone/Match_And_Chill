<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use App\Service\AvatarUploader;
use App\Form\RegistrationFormType;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, AvatarUploader $avatarUploader): Response
    {
        
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
       

        if ($form->isSubmitted() && $form->isValid()) {

            //Vérification honeyPot
            if (empty($form['honeyPot'])) {

                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                 // Gérer l'upload de l'avatar
                $avatarFile = $form->get('avatar')->getData();
                if ($avatarFile) {
                    try {
                        $avatarFileName = $avatarUploader->upload($avatarFile);
                        $user->setAvatar($avatarFileName);
                    } catch (\Exception $e) {
                        // Ajouter un message flash ou gérer l'erreur
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'avatar: '.$e->getMessage());

                        return $this->redirectToRoute('app_register');
                    }
                } else {
                    // Utiliser l'avatar par défaut si aucun fichier n'est télécharger 
                    $avatarFileName = 'user.svg';
                }
                // on set le nom du fichier de l'avatar 
                $user->setAvatar($avatarFileName);

                $entityManager->persist($user);
                $entityManager->flush();

                // generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('admin@matchandchill.com', 'admin'))
                        ->to($user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );
                // do anything else you need here, like send an email
                $this->addFlash('success', 'Inscription réussi');

                return $this->redirectToRoute('app_login');
                
            } else {
                $this->addFlash('danger', 'Are you a bot? ');

                return $this->redirectToRoute('app_register');
            }   
        }
         

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }

    #[Route('/register/conditions', name: 'app_conditions')]
    public function condition () {

        $checked = 'checked';

       // Redirige vers la page d'inscription avec la case cochée
       return $this->render('registration/conditions.html.twig', [
        'checked' =>  $checked,
    ]);
       
    }
}
