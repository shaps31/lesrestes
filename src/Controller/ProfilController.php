<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profil')]
#[IsGranted('ROLE_USER')]
class ProfilController extends AbstractController
{
    #[Route('/', name: 'app_profil')]
    public function index(RecetteRepository $recetteRepository): Response
    {
        $user = $this->getUser();
        $mesRecettes = $recetteRepository->findBy(['user' => $user], ['dateCreation' => 'DESC']);
        
        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'mesRecettes' => $mesRecettes,
            'mesFavoris' => [], // À implémenter plus tard
        ]);
    }
}