<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Form\RecetteType;
use App\Entity\Commentaire;
use App\Repository\IngredientRepository;
use App\Form\CommentaireType;
use App\Form\SearchAdvancedType;
use App\Repository\FavoriRepository;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/recette')]
final class RecetteController extends AbstractController
{
    #[Route('/recettes', name: 'app_recette_index')]
    public function index(
        Request $request,
        RecetteRepository $recetteRepository,
        PaginatorInterface $paginator
    ): Response {
    
        $searchForm = $this->createForm(SearchAdvancedType::class);
        $searchForm->handleRequest($request);
        
        $criteria = [];
        $orderBy = ['dateCreation' => 'DESC'];
        
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData();
            
            if ($data['query']) {
                $criteria['query'] = $data['query'];
            }
            if ($data['categorie']) {
                $criteria['categorie'] = $data['categorie'];
            }
            if ($data['difficulte']) {
                $criteria['difficulte'] = $data['difficulte'];
            }
            if ($data['tempsMax']) {
                $criteria['tempsMax'] = $data['tempsMax'];
            }
            if ($data['tri']) {
                switch ($data['tri']) {
                    case 'date_desc':
                        $orderBy = ['dateCreation' => 'DESC'];
                        break;
                    case 'date_asc':
                        $orderBy = ['dateCreation' => 'ASC'];
                        break;
                    case 'notes_desc':
                        $orderBy = ['moyenneNotes' => 'DESC'];
                        break;
                }
            }
        }
        
        $queryBuilder = $recetteRepository->findWithFiltersQueryBuilder($criteria, $orderBy);
        
        $recettes = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            3
        );
        
        return $this->render('recette/index.html.twig', [
            'recettes' => $recettes,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    
    #[Route('/new', name: 'app_recette_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')] 
    public function new(Request $request, EntityManagerInterface $entityManager, IngredientRepository $ingredientRepository): Response
    {   
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour créer une recette.');
            return $this->redirectToRoute('app_login');
        }
        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recette->setUser($this->getUser());
        
            // Gestion des ingrédients
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
            $commentaire->setDateCreation(new \DatetimeImmutable());
            
            $entityManager->persist($commentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été publié !');
            return $this->redirectToRoute('app_recette_show', ['id' => $recette->getId()]);
        }

        return $this->render('recette/show.html.twig', [
            'recette' => $recette,
            'isFavorite' => $isFavorite,
            'commentaireForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_recette_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recette $recette, EntityManagerInterface $entityManager, IngredientRepository $ingredientRepository): Response
    {
        // Vérification que l'utilisateur est bien le propriétaire
        if ($recette->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier cette recette.');
            return $this->redirectToRoute('app_recette_index');
        }

        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pas besoin de réassigner le user en édition
            foreach ($form->getErrors(true) as $error) {
        dump($error->getMessage());
    }
            // Gestion des ingrédients (même logique que dans new)
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
                            }
                        }
                    }
                }
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'Recette modifiée avec succès !');
            return $this->redirectToRoute('app_recette_show', ['id' => $recette->getId()]);
        }

        return $this->render('recette/edit.html.twig', [
            'recette' => $recette,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_recette_delete', methods: ['POST'])]
    public function delete(Request $request, Recette $recette, EntityManagerInterface $entityManager): Response
    {
        // Vérification que l'utilisateur est bien le propriétaire
        if ($recette->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer cette recette.');
            return $this->redirectToRoute('app_recette_index');
        }
        

        if ($this->isCsrfTokenValid('delete'.$recette->getId(), $request->request->get('_token'))) {
            $entityManager->remove($recette);
            $entityManager->flush();
            
            $this->addFlash('success', 'Recette supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_recette_index');
    }
}