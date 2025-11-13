<?php

namespace App\Controller;

use App\Repository\IngredientRepository;
use App\Repository\RecetteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/recherche', name: 'app_search')]
    public function index(
        Request $request, 
        IngredientRepository $ingredientRepository, 
        RecetteRepository $recetteRepository
    ): Response {
        $ingredients = $ingredientRepository->findAll();
        $selectedIngredients = [];
        $recettes = [];
        $debug = '';
        
        $query = $request->query->get('q');
        if ($query) {
            $debug .= " Recherche textuelle: '$query'\n";
            $searchTerms = array_filter(array_map('trim', explode(',', $query)));
            
            if (!empty($searchTerms)) {
                $qb = $ingredientRepository->createQueryBuilder('i');
                $orConditions = $qb->expr()->orX();
                
                foreach ($searchTerms as $index => $term) {
                    $orConditions->add("i.nom LIKE :term_$index");
                    $qb->setParameter("term_$index", '%' . $term . '%');
                }
                
                $selectedIngredients = $qb->where($orConditions)
                    ->getQuery()
                    ->getResult();
                
                $debug .= " Termes trouvés: " . count($selectedIngredients) . " ingrédients\n";
                foreach ($selectedIngredients as $ing) {
                    $debug .= "- " . $ing->getNom() . " (ID: " . $ing->getId() . ")\n";
                }
            }
        }
        
        $ingredientIds = $request->query->get('ingredients');
        if ($ingredientIds) {
            $debug .= " Recherche par IDs: $ingredientIds\n";
            $selectedIngredientIds = explode(',', $ingredientIds);
            $selectedIngredientsById = $ingredientRepository->findBy(['id' => $selectedIngredientIds]);
            
            foreach ($selectedIngredientsById as $ingredient) {
                if (!in_array($ingredient, $selectedIngredients, true)) {
                    $selectedIngredients[] = $ingredient;
                }
            }
        }
        
        if (!empty($selectedIngredients)) {
            $selectedIngredientIds = array_map(fn($i) => $i->getId(), $selectedIngredients);
            $debug .= " IDs finaux: " . implode(', ', $selectedIngredientIds) . "\n";
            
            $recettes = $recetteRepository->findByIngredients($selectedIngredientIds, requireAll: true);
            
            $debug .= " Recettes trouvées: " . count($recettes) . "\n";
            foreach ($recettes as $r) {
                $debug .= "- " . $r->getNom() . "\n";
            }
        }
        
        if ($this->getParameter('kernel.debug')) {
            dump($debug, $selectedIngredients, $recettes); 
        }
        
        return $this->render('search/recherche.html.twig', [
            'ingredients' => $ingredients,
            'selectedIngredients' => $selectedIngredients,
            'recettes' => $recettes,
            'debug' => $debug, 
        ]);
    }
}