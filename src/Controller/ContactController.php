<?php

namespace App\Controller;

use App\Form\ContactFormType;

use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController


{   


    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {
            // vérification du honeypot
            $honeyPot = $form->get('pays')->getData();
            if ($honeyPot) {
                return $this->redirectToRoute('app_home');
            }

            // on recupère les données du formulaire
            $pseudo = $form->get('pseudo')->getData();
            $message = $form->get('message')->getData();

            // Envoi du mail
            $senderEmail = $form->get('email')->getData();
            $emailMessage = (new Email())
                ->from($senderEmail)
                ->to('admin@matchandchill.com')
                ->subject('Nouveau message de ' . $pseudo)
                ->text($message);

            $mailer->send($emailMessage);

            $this->addFlash('success', 'Votre message a bien été envoyé !');

            return $this->redirectToRoute('app_contact');
        } else {
            $this->addFlash('error', 'Votre message n\'a pas pu être envoyé.');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
