<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecetteRepository $recetteRepository): Response
    {
        // Récupérer les 3 dernières recettes
        $dernieresRecettes = $recetteRepository->findBy([], ['dateCreation' => 'DESC'], 3);
        
        return $this->render('home/index.html.twig', [
            'dernieresRecettes' => $dernieresRecettes,
        ]);
    }
}