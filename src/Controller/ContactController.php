<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // TODO: Implémenter l'envoi d'email avec Symfony Mailer
            // Pour l'instant, je simule juste l'envoi
            
            // Simule l'envoi
            $this->addFlash('success', 'Votre message a ete envoye avec succes ! Nous vous repondrons dans les plus brefs delais.');
            
            // Logger l'information (pour développement)
            error_log(sprintf(
                "Contact form submission - From: %s (%s), Subject: %s, Message: %s",
                $data['nom'],
                $data['email'],
                $data['sujet'],
                substr($data['message'], 0, 50) . '...'
            ));
            
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
    }
}