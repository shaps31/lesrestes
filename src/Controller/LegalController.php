<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('legal/mentions-legales.html.twig');
    }

    #[Route('/politique-confidentialite', name: 'app_politique_confidentialite')]
    public function politiqueConfidentialite(): Response
    {
        return $this->render('legal/politique-confidentialite.html.twig');
    }

    #[Route('/conditions-generales-utilisation', name: 'app_cgu')]
    public function cgu(): Response
    {
        return $this->render('legal/cgu.html.twig');
    }
}