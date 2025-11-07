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
    public function index(Request $request, IngredientRepository $ingredientRepository, RecetteRepository $recetteRepository): Response
{
    $ingredients = $ingredientRepository->findAll();
    $selectedIngredients = [];
    $recettes = [];
    
    // Recherche textuelle depuis l'accueil
    $query = $request->query->get('q');
    if ($query) {
        // Convertir la recherche textuelle en ingrédients
        $searchTerms = explode(',', strtolower($query));
        foreach ($searchTerms as $term) {
            $term = trim($term);
            $ingredient = $ingredientRepository->findOneBy(['nom' => ucfirst($term)]);
            if ($ingredient && !in_array($ingredient, $selectedIngredients)) {
                $selectedIngredients[] = $ingredient;
            }
        }
    }
    
    // Recherche par IDs d'ingrédients
    $ingredientIds = $request->query->get('ingredients');
    if ($ingredientIds) {
        $selectedIngredientIds = explode(',', $ingredientIds);
        $selectedIngredients = array_merge($selectedIngredients, $ingredientRepository->findBy(['id' => $selectedIngredientIds]));
    }
    
    // Rechercher les recettes si des ingrédients sont sélectionnés
    if (!empty($selectedIngredients)) {
        $selectedIngredientIds = array_map(fn($i) => $i->getId(), $selectedIngredients);
        $recettes = $recetteRepository->findByIngredients($selectedIngredientIds);
    }
    
    return $this->render('search/index.html.twig', [
        'ingredients' => $ingredients,
        'selectedIngredients' => $selectedIngredients,
        'recettes' => $recettes,
    ]);
}
    
}