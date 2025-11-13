<?php

namespace App\Controller;

use App\Repository\IngredientRepository;
use App\Repository\RecetteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

class SearchController extends AbstractController
{
    #[Route('/recherche', name: 'app_search')]
    public function index(
    Request $request, 
    IngredientRepository $ingredientRepository, 
    RecetteRepository $recetteRepository,
    PaginatorInterface $paginator
): Response {

    $qbIngredients = $ingredientRepository->createQueryBuilder('i')
        ->orderBy('i.nom', 'ASC');

    $ingredients = $paginator->paginate(
        $qbIngredients,
        $request->query->getInt('page', 1),
        3
    );

    $selectedIngredients = [];
    $recettes = [];

    $query = $request->query->get('q');
    if ($query) {
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
        }
    }

    $ingredientIds = $request->query->get('ingredients');
    if ($ingredientIds) {
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

        $recettes = $recetteRepository->findByIngredients($selectedIngredientIds, requireAll: true);
    }

    return $this->render('search/recherche.html.twig', [
        'ingredients' => $ingredients,
        'selectedIngredients' => $selectedIngredients,
        'recettes' => $recettes,
    ]);
}
}