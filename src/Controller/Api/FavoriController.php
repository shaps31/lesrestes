<?php

namespace App\Controller\Api;

use App\Entity\Favori;
use App\Repository\FavoriRepository;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/favori')]
#[IsGranted('ROLE_USER')]
class FavoriController extends AbstractController
{
    #[Route('/toggle/{id}', name: 'api_favori_toggle', methods: ['POST'])]
    public function toggle(int $id, RecetteRepository $recetteRepository, FavoriRepository $favoriRepository, EntityManagerInterface $em): JsonResponse
    {
        $recette = $recetteRepository->find($id);
        if (!$recette) {
            return new JsonResponse(['error' => 'Recette non trouvée'], 404);
        }

        $user = $this->getUser();
        $favori = $favoriRepository->findOneBy(['user' => $user, 'recette' => $recette]);

        if ($favori) {
            // Retirer des favoris
            $em->remove($favori);
            $isFavorite = false;
        } else {
            // Ajouter aux favoris
            $favori = new Favori();
            $favori->setUser($user)
                   ->setRecette($recette);
            $em->persist($favori);
            $isFavorite = true;
        }

        $em->flush();

        return new JsonResponse([
            'success' => true,
            'isFavorite' => $isFavorite,
            'message' => $isFavorite ? 'Ajouté aux favoris' : 'Retiré des favoris'
        ]);
    }
}