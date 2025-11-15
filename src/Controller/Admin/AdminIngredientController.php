<?php

namespace App\Controller\Admin;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/ingredients')]
#[IsGranted('ROLE_ADMIN')]
class AdminIngredientController extends AbstractController
{
    #[Route('', name: 'app_admin_ingredient_index', methods: ['GET'])]
    public function index(
        Request $request,
        IngredientRepository $ingredientRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search', '');
        
        $queryBuilder = $ingredientRepository->createQueryBuilder('i')
            ->orderBy('i.nom', 'ASC');
        
        if ($search) {
            $queryBuilder
                ->where('i.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );
        
        return $this->render('admin/ingredient/index.html.twig', [
            'ingredients' => $pagination,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_admin_ingredient_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ingredient);
            $entityManager->flush();

            $this->addFlash('success', 'L\'ingredient a ete ajoute avec succes.');

            return $this->redirectToRoute('app_admin_ingredient_index');
        }

        return $this->render('admin/ingredient/new.html.twig', [
            'ingredient' => $ingredient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_ingredient_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Ingredient $ingredient,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'ingredient a ete modifie avec succes.');

            return $this->redirectToRoute('app_admin_ingredient_index');
        }

        return $this->render('admin/ingredient/edit.html.twig', [
            'ingredient' => $ingredient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_ingredient_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Ingredient $ingredient,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $ingredient->getId(), $request->request->get('_token'))) {
            // Vérifier si l'ingrédient est utilisé dans des recettes
            if ($ingredient->getRecetteIngredients()->count() > 0) {
                $this->addFlash('danger', 'Impossible de supprimer cet ingredient car il est utilise dans des recettes.');
            } else {
                $entityManager->remove($ingredient);
                $entityManager->flush();
                $this->addFlash('success', 'L\'ingredient a ete supprime avec succes.');
            }
        }

        return $this->redirectToRoute('app_admin_ingredient_index');
    }
}