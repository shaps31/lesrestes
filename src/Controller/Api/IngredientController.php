<?php

namespace App\Controller\Api;

use App\Repository\IngredientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/ingredients')]
class IngredientController extends AbstractController
{
    #[Route('/search', name: 'api_ingredients_search', methods: ['GET'])]
    public function search(Request $request, IngredientRepository $ingredientRepository): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        if (strlen($query) < 2) {
            return new JsonResponse([]);
        }
        
        $ingredients = $ingredientRepository->createQueryBuilder('i')
            ->where('i.nom LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
        
        $data = [];
        foreach ($ingredients as $ingredient) {
            $data[] = [
                'id' => $ingredient->getId(),
                'nom' => $ingredient->getNom(),
                'unite' => $ingredient->getUnite()
            ];
        }
        
        return new JsonResponse($data);
    }
}