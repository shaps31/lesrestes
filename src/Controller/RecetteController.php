<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Form\RecetteType;
use App\Entity\Commentaire;
use App\Repository\IngredientRepository;
use App\Form\CommentaireType;
use App\Repository\FavoriRepository;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/recette')]
final class RecetteController extends AbstractController
{
    #[Route(name: 'app_recette_index', methods: ['GET'])]
    public function index(RecetteRepository $recetteRepository): Response
    {
        return $this->render('recette/index.html.twig', [
            'recettes' => $recetteRepository->findAll(),
        ]);
    }
    
    #[Route('/new', name: 'app_recette_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, IngredientRepository $ingredientRepository): Response
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recette->setUser($this->getUser());
        
            $submittedData = $request->request->all();
            if (isset($submittedData['recette']['recetteIngredients'])) {
                foreach ($submittedData['recette']['recetteIngredients'] as $index => $ingredientData) {
                    $ingredientId = $ingredientData['ingredient_id'] ?? null;
                
                    if ($ingredientId && $ingredientId !== '') {
                        $ingredient = $ingredientRepository->find($ingredientId);
                        
                    if ($ingredient) {
                        $recetteIngredients = $recette->getRecetteIngredients()->toArray();
                        if (isset($recetteIngredients[$index])) {
                            $recetteIngredients[$index]->setIngredient($ingredient);
                            $recetteIngredients[$index]->setRecette($recette);
                        }
                    }
                }
            }
        }
        
        $entityManager->persist($recette);
        $entityManager->flush();

        $this->addFlash('success', 'Recette créée avec succès !');
        
        return $this->redirectToRoute('app_recette_show', ['id' => $recette->getId()]);
    }

        return $this->render('recette/new.html.twig', [
        'recette' => $recette,
        'form' => $form,
    ]);
    }    
    #[Route('/{id}', name: 'app_recette_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Recette $recette, FavoriRepository $favoriRepository, EntityManagerInterface $entityManager): Response
    {
    $recette->setVue($recette->getVue() + 1);
    $entityManager->flush();
    
    $isFavorite = false;
    if ($this->getUser()) {
        $isFavorite = $favoriRepository->findOneBy([
            'user' => $this->getUser(),
            'recette' => $recette
        ]) !== null;
    }

    
    $commentaire = new Commentaire();
    $form = $this->createForm(CommentaireType::class, $commentaire);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $commentaire->setUser($this->getUser());
        $commentaire->setRecette($recette);
        
        $entityManager->persist($commentaire);
        $entityManager->flush();

        $this->addFlash('success', 'Votre commentaire a été publié !');
        return $this->redirectToRoute('app_recette_show', ['id' => $recette->getId()]);
    }

    return $this->render('recette/show.html.twig', [
        'recette' => $recette,
        'isFavorite' => $isFavorite,
        'commentaireForm' => $form,
    ]);
    }

    #[Route('/{id}/edit', name: 'app_recette_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recette $recette, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recette->setUser($this->getUser());
            $entityManager->flush();

            return $this->redirectToRoute('app_recette_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recette/edit.html.twig', [
            'recette' => $recette,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_recette_delete', methods: ['POST'])]
    public function delete(Request $request, Recette $recette, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$recette->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($recette);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_recette_index', [], Response::HTTP_SEE_OTHER);
    }
}
