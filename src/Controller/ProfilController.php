<?php

namespace App\Controller;

use App\Form\UserProfileType;
use App\Repository\RecetteRepository;
use App\Repository\FavoriRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProfilController extends AbstractController
{
    public function __construct(
        private RecetteRepository $recetteRepository,
        private FavoriRepository $favoriRepository
    ) {
    }

    #[Route('/profil', name: 'app_profil')]
    public function index(
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Mes recettes avec pagination (3 par page)
        $mesRecettesQuery = $this->recetteRepository->createQueryBuilder('r')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery();
        
        $mesRecettes = $paginator->paginate(
            $mesRecettesQuery,
            $request->query->getInt('page', 1),
            3
        );
        
        // Mes favoris avec pagination (3 par page)
        $mesFavorisQuery = $this->favoriRepository->createQueryBuilder('f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->orderBy('f.dateAjout', 'DESC')
            ->getQuery();
        
        $mesFavoris = $paginator->paginate(
            $mesFavorisQuery,
            $request->query->getInt('page_favoris', 1),
            3
        );
        
        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'mesRecettes' => $mesRecettes,
            'mesFavoris' => $mesFavoris,
        ]);
    }

    #[Route('/profil/modifier', name: 'app_profil_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger
    ): Response {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();
            
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                
                try {
                    $photoFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/profils',
                        $newFilename
                    );
                    
                    if ($user->getPhoto()) {
                        $oldPhoto = $this->getParameter('kernel.project_dir').'/public/uploads/profils/'.$user->getPhoto();
                        if (file_exists($oldPhoto)) {
                            unlink($oldPhoto);
                        }
                    }
                    
                    $user->setPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de la photo');
                }
            }
            
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();
            
            if ($newPassword) {
                if (!$currentPassword) {
                    $this->addFlash('danger', 'Veuillez entrer votre mot de passe actuel');
                    return $this->render('profil/edit.html.twig', [
                        'form' => $form,
                        'user' => $user
                    ]);
                }
                
                if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('danger', 'Le mot de passe actuel est incorrect');
                    return $this->render('profil/edit.html.twig', [
                        'form' => $form,
                        'user' => $user
                    ]);
                }
                
                if ($newPassword !== $confirmPassword) {
                    $this->addFlash('danger', 'Les mots de passe ne correspondent pas');
                    return $this->render('profil/edit.html.twig', [
                        'form' => $form,
                        'user' => $user
                    ]);
                }
                
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
            }
            
            $em->flush();
            
            $this->addFlash('success', 'Profil modifié avec succès !');
            return $this->redirectToRoute('app_profil');
        }
        
        return $this->render('profil/edit.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }
}