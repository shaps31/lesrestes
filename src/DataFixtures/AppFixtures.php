<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Categorie;
use App\Entity\Ingredient;
use App\Entity\Recette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Utilisateurs
        $admin = new User();
        $admin->setEmail('admin@lesrestes.com');
        $admin->setNom('Admin');
        $admin->setPrenom('Super');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setIsVerified(true);
        $manager->persist($admin);

        $chef = new User();
        $chef->setEmail('chef@lesrestes.com');
        $chef->setNom('Dupont');
        $chef->setPrenom('Marie');
        $chef->setPassword($this->passwordHasher->hashPassword($chef, 'chef123'));
        $chef->setIsVerified(true);
        $manager->persist($chef);

        // Catégories
        $entrees = new Categorie();
        $entrees->setNom('Entrées');
        $entrees->setDescription('Plats pour commencer le repas');
        $entrees->setCouleur('#28a745');
        $manager->persist($entrees);

        $plats = new Categorie();
        $plats->setNom('Plats principaux');
        $plats->setDescription('Plats de résistance');
        $plats->setCouleur('#dc3545');
        $manager->persist($plats);

        $desserts = new Categorie();
        $desserts->setNom('Desserts');
        $desserts->setDescription('Plats sucrés');
        $desserts->setCouleur('#ffc107');
        $manager->persist($desserts);

        // Ingrédients
        $tomates = new Ingredient();
        $tomates->setNom('Tomates');
        $tomates->setUnite('kg');
        $tomates->setDescription('Tomates fraîches');
        $manager->persist($tomates);

        $mozzarella = new Ingredient();
        $mozzarella->setNom('Mozzarella');
        $mozzarella->setUnite('g');
        $mozzarella->setDescription('Fromage italien');
        $manager->persist($mozzarella);

        $basilic = new Ingredient();
        $basilic->setNom('Basilic');
        $basilic->setUnite('branches');
        $basilic->setDescription('Herbe aromatique');
        $manager->persist($basilic);

        $pates = new Ingredient();
        $pates->setNom('Pâtes');
        $pates->setUnite('g');
        $pates->setDescription('Pâtes italiennes');
        $manager->persist($pates);

        $oeufs = new Ingredient();
        $oeufs->setNom('Œufs');
        $oeufs->setUnite('unités');
        $oeufs->setDescription('Œufs frais');
        $manager->persist($oeufs);

        $manager->flush();

        // Recettes
        $salade = new Recette();
        $salade->setNom('Salade tomates mozza');
        $salade->setDescription('Salade fraîche et savoureuse');
        $salade->setEtapes("1. Couper les tomates\n2. Ajouter la mozzarella\n3. Assaisonner avec basilic");
        $salade->setTempsCuisson(0);
        $salade->setNombrePersonnes(4);
        $salade->setDifficulte(1);
        $salade->setImage('https://via.placeholder.com/300x200');
        $salade->setCategorie($entrees);
        $salade->setUser($admin);
        $manager->persist($salade);

        $carbonara = new Recette();
        $carbonara->setNom('Pâtes carbonara');
        $carbonara->setDescription('Le grand classique italien');
        $carbonara->setEtapes("1. Cuire les pâtes\n2. Battre les œufs\n3. Mélanger hors du feu\n4. Servir");
        $carbonara->setTempsCuisson(15);
        $carbonara->setNombrePersonnes(2);
        $carbonara->setDifficulte(2);
        $carbonara->setCategorie($plats);
        $carbonara->setUser($chef);
        $manager->persist($carbonara);

        $manager->flush();
    }
}