<?php

namespace App\Controller;

use App\Entity\Commentaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commentaire')]
#[IsGranted('ROLE_USER')]
class CommentaireController extends AbstractController
{
    #[Route('/{id}/delete', name: 'app_commentaire_delete', methods: ['POST'])]
    public function delete(Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur connecté est bien l'auteur
        if ($commentaire->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres commentaires.');
        }

        $recetteId = $commentaire->getRecette()->getId();
        
        $entityManager->remove($commentaire);
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire supprimé avec succès.');
        
        return $this->redirectToRoute('app_recette_show', ['id' => $recetteId]);
    }
}