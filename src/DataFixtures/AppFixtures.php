<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Categorie;
use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\RecetteIngredient;
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
        $user = new User();
        $user->setEmail('admin@lesrestes.com');
        $user->setNom('Admin');
        $user->setPrenom('Super');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'admin123'));
        $user->setIsVerified(true);
        $manager->persist($user);

        $categories = [
            'Plats principaux' => '#dc3545',
            'Entrées' => '#28a745',
            'Desserts' => '#ffc107',
        ];
        
        $catObjects = [];
        foreach ($categories as $nom => $couleur) {
            $cat = new Categorie();
            $cat->setNom($nom);
            $cat->setDescription('Catégorie ' . $nom);
            $cat->setCouleur($couleur);
            $manager->persist($cat);
            $catObjects[$nom] = $cat;
        }

        $ingredients = [];
        $ingredientNames = [
            'Tomates', 'Mozzarella', 'Basilic', 'Ail', 'Œufs', 'Beurre', 'Sel',
            'Pommes', 'Sucre', 'Jambon', 'Fromage', 'Laitue', 'Riz', 'Champignons',
            'Crème fraîche', 'Parmesan', 'Farine', 'Citron', 'Pâtes', 'Oignons',
            'Carottes', 'Huile d\'olive', 'Piment', 'Poulet', 'Bœuf', 'Poisson',
            'Pâte brisée', 'Haricots', 'Aubergines', 'Chocolat', 'Vanille'
        ];
        
        foreach ($ingredientNames as $name) {
            $ing = new Ingredient();
            $ing->setNom($name);
            $ing->setUnite('g');
            $ing->setDescription('Ingrédient pour recettes');
            $manager->persist($ing);
            $ingredients[$name] = $ing;
        }

        $existingImages = ['pizza.jpg', 'lasagnes.jpg', 'tarte.jpg', 'salade.jpg', 'poulet.jpg'];
        $recipes = [
            ['nom' => 'Tarte aux pommes', 'cat' => 'Desserts', 'ing' => ['Pommes', 'Sucre', 'Beurre', 'Farine']],
            ['nom' => 'Quiche lorraine', 'cat' => 'Plats principaux', 'ing' => ['Œufs', 'Jambon', 'Fromage']],
            ['nom' => 'Gratin dauphinois', 'cat' => 'Plats principaux', 'ing' => ['Pommes', 'Crème fraîche', 'Fromage']],
            ['nom' => 'Ratatouille provençale', 'cat' => 'Plats principaux', 'ing' => ['Tomates', 'Ail']],
            ['nom' => 'Poulet rôti aux herbes', 'cat' => 'Plats principaux', 'ing' => ['Poulet', 'Ail']],
            ['nom' => 'Lasagnes bolognaise', 'cat' => 'Plats principaux', 'ing' => ['Pâtes', 'Fromage', 'Tomates']],
            ['nom' => 'Pizza margherita', 'cat' => 'Plats principaux', 'ing' => ['Tomates', 'Mozzarella', 'Basilic']],
            ['nom' => 'Salade niçoise', 'cat' => 'Entrées', 'ing' => ['Laitue', 'Œufs']],
            ['nom' => 'Crêpes bretonnes', 'cat' => 'Desserts', 'ing' => ['Farine', 'Œufs', 'Beurre']],
            ['nom' => 'Cake au citron', 'cat' => 'Desserts', 'ing' => ['Farine', 'Sucre', 'Citron']],
            ['nom' => 'Soupe de légumes', 'cat' => 'Entrées', 'ing' => ['Carottes', 'Tomates', 'Ail']],
            ['nom' => 'Poisson grillé', 'cat' => 'Plats principaux', 'ing' => ['Poisson', 'Huile d\'olive', 'Citron']],
            ['nom' => 'Omelette aux champignons', 'cat' => 'Plats principaux', 'ing' => ['Œufs', 'Champignons', 'Beurre']],
            ['nom' => 'Riz cantonais', 'cat' => 'Plats principaux', 'ing' => ['Riz', 'Œufs', 'Jambon']],
            ['nom' => 'Pâtes carbonara', 'cat' => 'Plats principaux', 'ing' => ['Pâtes', 'Œufs', 'Fromage']],
            ['nom' => 'Sandwich club', 'cat' => 'Plats principaux', 'ing' => ['Jambon', 'Fromage', 'Laitue']],
            ['nom' => 'Muffins aux myrtilles', 'cat' => 'Desserts', 'ing' => ['Farine', 'Sucre', 'Œufs']],
            ['nom' => 'Steak-frites', 'cat' => 'Plats principaux', 'ing' => ['Bœuf', 'Pommes', 'Beurre']],
            ['nom' => 'Risotto aux champignons', 'cat' => 'Plats principaux', 'ing' => ['Riz', 'Champignons', 'Crème fraîche']],
            ['nom' => 'Burger maison', 'cat' => 'Plats principaux', 'ing' => ['Bœuf', 'Fromage', 'Laitue']],
            ['nom' => 'Curry de poulet', 'cat' => 'Plats principaux', 'ing' => ['Poulet', 'Riz', 'Crème fraîche']],
            ['nom' => 'Paella valenciana', 'cat' => 'Plats principaux', 'ing' => ['Riz', 'Tomates', 'Ail']],
            ['nom' => 'Couscous royal', 'cat' => 'Plats principaux', 'ing' => ['Bœuf', 'Riz', 'Tomates']],
            ['nom' => 'Chili con carne', 'cat' => 'Plats principaux', 'ing' => ['Bœuf', 'Tomates', 'Piment']],
            ['nom' => 'Pad thaï', 'cat' => 'Plats principaux', 'ing' => ['Riz', 'Œufs', 'Piment']],
            ['nom' => 'Sushi maison', 'cat' => 'Entrées', 'ing' => ['Riz', 'Ail', 'Œufs']],
            ['nom' => 'Wok de légumes', 'cat' => 'Plats principaux', 'ing' => ['Carottes', 'Tomates', 'Ail']],
            ['nom' => 'Brochettes de poulet', 'cat' => 'Plats principaux', 'ing' => ['Poulet', 'Ail', 'Citron']],
            ['nom' => 'Moussaka grecque', 'cat' => 'Plats principaux', 'ing' => ['Aubergines', 'Tomates', 'Fromage']],
            ['nom' => 'Tiramisu', 'cat' => 'Desserts', 'ing' => ['Œufs', 'Sucre', 'Crème fraîche']],
            ['nom' => 'Fondue savoyarde', 'cat' => 'Plats principaux', 'ing' => ['Fromage', 'Beurre', 'Œufs']],
            ['nom' => 'Raclette party', 'cat' => 'Plats principaux', 'ing' => ['Fromage', 'Pommes', 'Jambon']],
            ['nom' => 'Crumble aux fruits', 'cat' => 'Desserts', 'ing' => ['Pommes', 'Sucre', 'Beurre']],
            ['nom' => 'Tarte tatin', 'cat' => 'Desserts', 'ing' => ['Pommes', 'Sucre', 'Beurre']],
            ['nom' => 'Clafoutis aux cerises', 'cat' => 'Desserts', 'ing' => ['Œufs', 'Farine', 'Sucre']],
            ['nom' => 'Brownie chocolat', 'cat' => 'Desserts', 'ing' => ['Chocolat', 'Farine', 'Œufs']],
            ['nom' => 'Cheesecake framboise', 'cat' => 'Desserts', 'ing' => ['Fromage', 'Sucre', 'Œufs']],
            ['nom' => 'Mousse au chocolat', 'cat' => 'Desserts', 'ing' => ['Chocolat', 'Œufs', 'Sucre']],
            ['nom' => 'Panna cotta vanille', 'cat' => 'Desserts', 'ing' => ['Vanille', 'Sucre', 'Crème fraîche']],
            ['nom' => 'Crème brûlée', 'cat' => 'Desserts', 'ing' => ['Œufs', 'Sucre', 'Crème fraîche']],
            ['nom' => 'Bœuf bourguignon', 'cat' => 'Plats principaux', 'ing' => ['Bœuf', 'Carottes', 'Ail']],
            ['nom' => 'Blanquette de veau', 'cat' => 'Plats principaux', 'ing' => ['Veau', 'Crème fraîche', 'Œufs']],
            ['nom' => 'Pot-au-feu', 'cat' => 'Plats principaux', 'ing' => ['Bœuf', 'Carottes', 'Tomates']],
            ['nom' => 'Cassoulet toulousain', 'cat' => 'Plats principaux', 'ing' => ['Haricots', 'Tomates', 'Ail']],
            ['nom' => 'Bouillabaisse', 'cat' => 'Plats principaux', 'ing' => ['Poisson', 'Tomates', 'Ail']],
            ['nom' => 'Magret de canard', 'cat' => 'Plats principaux', 'ing' => ['Canard', 'Tomates', 'Poivre']],
            ['nom' => 'Foie gras poêlé', 'cat' => 'Entrées', 'ing' => ['Foie gras', 'Œufs', 'Farine']],
            ['nom' => 'Tartare de saumon', 'cat' => 'Entrées', 'ing' => ['Saumon', 'Citron', 'Œufs']],
            ['nom' => 'Carpaccio de bœuf', 'cat' => 'Entrées', 'ing' => ['Bœuf', 'Jus de citron', 'Œufs']],
            ['nom' => 'Velouté de butternut', 'cat' => 'Entrées', 'ing' => ['Butternut', 'Crème fraîche', 'Ail']],
        ];

        $imageIndex = 0;
        $totalImages = count($existingImages);

        foreach ($recipes as $data) {
            $recette = new Recette();
            $recette->setNom($data['nom']);
            $recette->setDescription('Recette délicieuse et facile à préparer');
            $recette->setEtapes("1. Préparer les ingrédients\n2. Suivre les étapes\n3. Servir chaud");
            $recette->setTempsCuisson(rand(10, 90));
            $recette->setNombrePersonnes(rand(2, 6));
            $recette->setDifficulte(rand(1, 3));
            
            $recette->setImage($existingImages[$imageIndex % $totalImages]);
            $imageIndex++;
            
            $recette->setCategorie($catObjects[$data['cat']]);
            $recette->setUser($user);
            $manager->persist($recette);

            foreach ($data['ing'] as $ingName) {
                if (!isset($ingredients[$ingName])) {
                    $ing = new Ingredient();
                    $ing->setNom($ingName);
                    $ing->setUnite('g');
                    $ing->setDescription('Ingrédient auto-créé');
                    $manager->persist($ing);
                    $ingredients[$ingName] = $ing;
                }

                $recetteIngredient = new RecetteIngredient();
                $recetteIngredient->setRecette($recette);
                $recetteIngredient->setIngredient($ingredients[$ingName]);
                $recetteIngredient->setQuantite(rand(50, 400));
                $manager->persist($recetteIngredient);
            }
        }

        $manager->flush();
    }
}