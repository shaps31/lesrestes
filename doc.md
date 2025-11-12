# DOCUMENTATION DE RÉALISATION
## PROJET LES RESTES - Application Anti-Gaspillage Alimentaire

**Auteur** : [Ton Nom]  
**Formation** : Titre Professionnel Développeur Web et Web Mobile - Niveau 5  
**Centre de formation** : Dawan Toulouse  
**Date de début** : [Date]  
**Graduation prévue** : Avril 2026  
**Status** :  En développement actif

---

##  Table des matières

1. [Introduction](#introduction)
2. [Setup Initial](#étape-1--setup-initial)
3. [Création des Entités](#étape-2--entités-principales)
4. [Système d'Authentification](#étape-3--authentification)
5. [Contrôleurs CRUD](#étape-4--contrôleurs-crud)
6. [Données de Test (Fixtures)](#étape-5--fixtures)
7. [Templates Bootstrap](#étape-6--templates-bootstrap)
8. [Système de Favoris](#étape-9--système-de-favoris)
9. [Upload d'Images et Optimisations](#étape-10--upload-dimages-et-optimisations-uiux)
10. [Système de Commentaires](#étape-11--système-de-commentaires)
11. [Améliorations UX](#étape-12--améliorations-ux)
12. [Prochaines Étapes](#prochaines-étapes)

---

## Introduction

### Contexte du projet

Les Restes est une plateforme web anti-gaspillage alimentaire qui permet aux utilisateurs de :
- Trouver des recettes en fonction des ingrédients disponibles
- Partager leurs propres recettes anti-gaspi
- Sauvegarder leurs recettes favorites
- Consulter des recettes par catégorie

### Stack technique

- **Backend** : Symfony 7.4
- **PHP** : Version 8.3.6
- **Base de données** : MySQL 8.0 (Docker)
- **Frontend** : Bootstrap 5, JavaScript vanilla
- **Assets** : Webpack Encore
- **Gestionnaire de dépendances** : Composer 2.8.12
- **CLI** : Symfony CLI 5.15.1
- **Versionning** : Git

---

## ÉTAPE 1 : SETUP INITIAL

### 1.1 Installation de l'environnement

J'ai commencé par installer tous les outils nécessaires :

```bash
# Vérification des versions installées
php -v          # PHP 8.3.6
composer -V     # Composer 2.8.12
symfony -V      # Symfony CLI 5.15.1
```

### 1.2 Création du projet Symfony

```bash
# Création du projet avec Symfony
symfony new lesrestes --version=stable

# Vérification de la version installée
cd lesrestes
php bin/console about
# Symfony 7.4 installé avec succès
```

### 1.3 Configuration de la base de données avec Docker

J'ai choisi Docker pour avoir un environnement MySQL reproductible.

**Fichier `docker-compose.yml` créé** :
```yaml
version: '3.8'

services:
  mysql:
    image: mysql:8.0
    container_name: lesrestes_mysql
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: lesrestes
      MYSQL_USER: lesrestes_user
      MYSQL_PASSWORD: lesrestes_pass
    ports:
      - "3307:3306"
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

**Configuration du fichier `.env`** :
```env
DATABASE_URL="mysql://lesrestes_user:lesrestes_pass@127.0.0.1:3307/lesrestes?serverVersion=8.0"
```

**Démarrage de la base de données** :
```bash
# Lancement du conteneur Docker
docker-compose up -d

# Création de la base de données
php bin/console doctrine:database:create
```

### 1.4 Initialisation Git

```bash
git init
git add .
git commit -m "Initial Symfony 7.4 setup with Docker MySQL"
git checkout -b feature/entities
```

---

## ÉTAPE 2 : ENTITÉS PRINCIPALES

### 2.1 Création de l'entité User avec sécurité

```bash
php bin/console make:user
```

**Configuration choisie** :
- Class name : `User`
- Store users in database : `yes`
- Unique identifier : `email`
- Password field : `yes`

### 2.2 Extension de l'entité User

J'ai ajouté les propriétés supplémentaires selon le cahier des charges :

```bash
php bin/console make:entity User
```

**Propriétés ajoutées** :
- `nom` (string, 50, not null)
- `prenom` (string, 50, not null)
- `bio` (text, nullable)
- `avatar` (string, 255, nullable)
- `dateInscription` (datetime_immutable, not null)
- `isVerified` (boolean, not null)

### 2.3 Création de l'entité Ingredient

```bash
php bin/console make:entity Ingredient
```

**Propriétés** :
- `nom` (string, 50, not null, unique)
- `unite` (string, 20, nullable)
- `description` (text, nullable)
- `dateCreation` (datetime_immutable, not null)

### 2.4 Création de l'entité Categorie

```bash
php bin/console make:entity Categorie
```

**Propriétés** :
- `nom` (string, 50, not null)
- `description` (text, nullable)
- `couleur` (string, 7, nullable)

### 2.5 Création de l'entité Recette

```bash
php bin/console make:entity Recette
```

**Propriétés** :
- `nom` (string, 100, not null)
- `description` (text, not null)
- `etapes` (text, not null)
- `image` (string, 255, nullable)
- `tempsCuisson` (integer, not null)
- `nombrePersonnes` (integer, not null)
- `difficulte` (integer, not null)
- `vue` (integer, not null, default 0)
- `dateCreation` (datetime_immutable, not null)

### 2.6 Ajout des relations entre entités

**Relations User ↔ Recette** :
```bash
php bin/console make:entity User
# Ajout relation: recettes (OneToMany vers Recette)

php bin/console make:entity Recette
# Ajout relation: user (ManyToOne vers User)
```

**Relations Recette ↔ Categorie** :
```bash
php bin/console make:entity Recette
# Ajout relation: categorie (ManyToOne vers Categorie, nullable: yes)
```

### 2.7 Création de l'entité RecetteIngredient (table de liaison)

```bash
php bin/console make:entity RecetteIngredient
```

**Propriétés** :
- `quantite` (string, 50, not null)
- `unite` (string, 20, nullable)
- `recette` (ManyToOne vers Recette)
- `ingredient` (ManyToOne vers Ingredient)

### 2.8 Ajout des constructeurs dans les entités

J'ai modifié manuellement les entités pour initialiser les dates et collections :

**Dans `src/Entity/User.php`** :
```php
public function __construct()
{
    $this->recettes = new ArrayCollection();
    $this->dateInscription = new \DateTimeImmutable();
}
```

**Dans `src/Entity/Ingredient.php`** :
```php
public function __construct()
{
    $this->dateCreation = new \DateTimeImmutable();
}
```

**Dans `src/Entity/Recette.php`** :
```php
public function __construct()
{
    $this->recetteIngredients = new ArrayCollection();
    $this->dateCreation = new \DateTimeImmutable();
    $this->vue = 0;
}
```

### 2.9 Première migration

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### 2.10 Commit et fusion de la branche

```bash
git add .
git commit -m "feat: Création entités User, Recette, Ingredient, Categorie avec relations"
git checkout master
git merge feature/entities
git branch -d feature/entities
```

---

## ÉTAPE 3 : AUTHENTIFICATION

### 3.1 Création d'une nouvelle branche

```bash
git checkout -b feature/authentication
```

### 3.2 Mise en place de l'authentification

```bash
php bin/console make:auth
```

**Configuration** :
- Authenticator type : `Login form authenticator`
- Controller class name : `SecurityController`
- Generate logout URL : `yes`

### 3.3 Création du formulaire d'inscription

```bash
php bin/console make:registration-form
```

**Options choisies** :
- Add @UniqueEntity validation : `yes`
- Send email verification : `no` (pour l'instant)
- Automatically authenticate : `yes`
- Redirect after registration : `/`

### 3.4 Installation du bundle de vérification email

```bash
composer require symfonycasts/verify-email-bundle
```

### 3.5 Modification du formulaire d'inscription

J'ai personnalisé `src/Form/RegistrationFormType.php` pour inclure nom et prénom :

```php
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('prenom', TextType::class, [
            'label' => 'Prénom',
            'attr' => ['class' => 'form-control']
        ])
        ->add('nom', TextType::class, [
            'label' => 'Nom',
            'attr' => ['class' => 'form-control']
        ])
        ->add('email')
        ->add('plainPassword', PasswordType::class, [
            'mapped' => false,
            'label' => 'Mot de passe',
            // ...
        ])
        ->add('agreeTerms', CheckboxType::class, [
            'mapped' => false,
        ]);
}
```

### 3.6 Tests de l'authentification

J'ai testé les pages suivantes :
- **Inscription** : `http://localhost:8004/register`
- **Connexion** : `http://localhost:8004/login`
- **Déconnexion** : `http://localhost:8004/logout`

### 3.7 Commit et fusion

```bash
git add .
git commit -m "feat: Système d'authentification complet avec inscription et login"
git checkout master
git merge feature/authentication
git branch -d feature/authentication
```

---

## ÉTAPE 4 : CONTRÔLEURS CRUD

### 4.1 Nouvelle branche

```bash
git checkout -b feature/crud-controllers
```

### 4.2 Création des CRUD

**CRUD Categorie** :
```bash
php bin/console make:crud Categorie
```

**CRUD Ingredient** :
```bash
php bin/console make:crud Ingredient
```

**CRUD Recette** :
```bash
php bin/console make:crud Recette
```

### 4.3 Création du contrôleur Home

```bash
php bin/console make:controller Home
```

### 4.4 Tests des CRUD

J'ai testé toutes les pages CRUD :
- Catégories : `http://127.0.0.1:8004/categorie/`
- Ingrédients : `http://127.0.0.1:8004/ingredient/`
- Recettes : `http://127.0.0.1:8004/recette/`

### 4.5 Correction du formulaire Recette

J'ai supprimé le champ `user` dans `src/Form/RecetteType.php` car il sera défini automatiquement :

```php
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('nom')
        ->add('description')
        ->add('etapes')
        // ->add('user')  // ← Supprimé
        ->add('categorie');
}
```

### 4.6 Commit et fusion

```bash
git add .
git commit -m "feat: CRUD complets pour Categorie, Ingredient et Recette"
git checkout master
git merge feature/crud-controllers
git branch -d feature/crud-controllers
```

---

## ÉTAPE 5 : FIXTURES

### 5.1 Nouvelle branche

```bash
git checkout -b feature/fixtures
```

### 5.2 Installation du bundle Fixtures

```bash
composer require --dev doctrine/doctrine-fixtures-bundle
```

### 5.3 Création des fixtures

```bash
php bin/console make:fixtures
# Nom : AppFixtures
```

### 5.4 Implémentation des fixtures

J'ai rempli `src/DataFixtures/AppFixtures.php` avec des données de test :

```php
public function load(ObjectManager $manager): void
{
    // Création d'utilisateurs
    $user1 = new User();
    $user1->setEmail('admin@lesrestes.com');
    $user1->setPrenom('Admin');
    $user1->setNom('Les Restes');
    $user1->setPassword($this->passwordHasher->hashPassword($user1, 'admin123'));
    $manager->persist($user1);

    // Création de catégories
    $categories = [
        ['nom' => 'Entrées', 'description' => 'Plats pour commencer', 'couleur' => '#28a745'],
        ['nom' => 'Plats', 'description' => 'Plats principaux', 'couleur' => '#007bff'],
        ['nom' => 'Desserts', 'description' => 'Desserts sucrés', 'couleur' => '#ffc107'],
    ];

    $categoriesObjects = [];
    foreach ($categories as $cat) {
        $categorie = new Categorie();
        $categorie->setNom($cat['nom']);
        $categorie->setDescription($cat['description']);
        $categorie->setCouleur($cat['couleur']);
        $manager->persist($categorie);
        $categoriesObjects[] = $categorie;
    }

    // Création d'ingrédients
    $ingredients = [
        ['nom' => 'Tomates', 'unite' => 'kg'],
        ['nom' => 'Oeufs', 'unite' => 'unité'],
        ['nom' => 'Fromage', 'unite' => 'g'],
        ['nom' => 'Pain', 'unite' => 'unité'],
        ['nom' => 'Pommes de terre', 'unite' => 'kg'],
    ];

    $ingredientsObjects = [];
    foreach ($ingredients as $ing) {
        $ingredient = new Ingredient();
        $ingredient->setNom($ing['nom']);
        $ingredient->setUnite($ing['unite']);
        $manager->persist($ingredient);
        $ingredientsObjects[] = $ingredient;
    }

    // Création de recettes
    $recette1 = new Recette();
    $recette1->setNom('Omelette aux tomates');
    $recette1->setDescription('Une omelette simple et savoureuse');
    $recette1->setEtapes("1. Battre les œufs\n2. Couper les tomates\n3. Cuire l'omelette");
    $recette1->setTempsCuisson(15);
    $recette1->setNombrePersonnes(2);
    $recette1->setDifficulte(1);
    $recette1->setUser($user1);
    $recette1->setCategorie($categoriesObjects[0]);
    $manager->persist($recette1);

    $manager->flush();
}
```

### 5.5 Chargement des fixtures

```bash
php bin/console doctrine:fixtures:load
```

### 5.6 Commit et fusion

```bash
git add .
git commit -m "feat: Fixtures avec données de test complètes"
git checkout master
git merge feature/fixtures
git branch -d feature/fixtures
```

---

## ÉTAPE 6 : TEMPLATES BOOTSTRAP

### 6.1 Nouvelle branche

```bash
git checkout -b feature/bootstrap-templates
```

### 6.2 Installation de Bootstrap et Webpack Encore

```bash
composer require symfony/webpack-encore-bundle
npm install
npm install bootstrap @popperjs/core
npm install --save-dev sass-loader sass
```

### 6.3 Configuration Webpack Encore

**Fichier `webpack.config.js`** :
```javascript
const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader();

module.exports = Encore.getWebpackConfig();
```

**Fichier `assets/app.js`** :
```javascript
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import './styles/app.css';
```

**Fichier `assets/styles/app.css`** :
```css
:root {
    --primary-color: #1E5128;
    --secondary-color: #4E9F3D;
}

.navbar-brand {
    font-weight: bold;
    color: var(--primary-color) !important;
}

.btn-success {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}
```

### 6.4 Build des assets

```bash
npm run dev
```

### 6.5 Création du template de base avec navigation

J'ai créé `templates/base.html.twig` avec une navigation Bootstrap complète :

```twig
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}Les Restes{% endblock %}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    {% block stylesheets %}
        {{ encore_entry_link_tags('app') }}
    {% endblock %}
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="{{ path('app_home') }}">
                 Les Restes
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ path('app_home') }}">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ path('app_recette_index') }}">Recettes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ path('app_ingredient_index') }}">Ingrédients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    {% if app.user %}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ path('app_profil') }}">
                                <i class="bi bi-person-circle"></i> Profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ path('app_logout') }}">Déconnexion</a>
                        </li>
                    {% else %}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ path('app_login') }}">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-success text-white" href="{{ path('app_register') }}">
                                S'inscrire
                            </a>
                        </li>
                    {% endif %}
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <div class="container my-4">
        {% block body %}{% endblock %}
    </div>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">© 2025 Les Restes - Anti-gaspi culinaire</p>
        </div>
    </footer>

    {% block javascripts %}
        {{ encore_entry_script_tags('app') }}
    {% endblock %}
</body>
</html>
```

### 6.6 Page d'accueil avec Hero et recherche

J'ai créé `templates/home/index.html.twig` selon les wireframes :

```twig
{% extends 'base.html.twig' %}

{% block title %}Accueil - Les Restes{% endblock %}

{% block body %}
<!-- Hero Section -->
<div class="hero-section text-center py-5 mb-5">
    <h1 class="display-4 mb-4">
        Transformez vos restes en délicieuses recettes
    </h1>
    
    <!-- Barre de recherche -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="{{ path('app_search') }}" method="GET">
                <div class="input-group input-group-lg mb-4">
                    <input type="text" class="form-control" name="q" 
                           placeholder="Entrez vos ingrédients (ex: tomates, œufs, fromage...)">
                    <button class="btn btn-success" type="submit">
                        <i class="bi bi-search"></i> Trouver des recettes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Section Dernières recettes -->
<div class="row">
    <div class="col-12">
        <h3 class="mb-4">Dernières recettes ajoutées</h3>
    </div>
</div>

<div class="row">
    {% for recette in dernieresRecettes %}
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                {% if recette.image %}
                    <img src="/uploads/recettes/{{ recette.image }}" 
                         class="card-img-top" 
                         style="height: 200px; object-fit: cover;" 
                         alt="{{ recette.nom }}">
                {% else %}
                    <img src="https://source.unsplash.com/300x200/?food" 
                         class="card-img-top" 
                         style="height: 200px; object-fit: cover;" 
                         alt="{{ recette.nom }}">
                {% endif %}
                <div class="card-body">
                    <h5 class="card-title">{{ recette.nom }}</h5>
                    <p class="card-text text-muted small">
                        {{ recette.description|slice(0, 50) }}...
                    </p>
                    <a href="{{ path('app_recette_show', {'id': recette.id}) }}" 
                       class="btn btn-outline-success btn-sm">
                        <i class="bi bi-eye"></i> Voir
                    </a>
                </div>
            </div>
        </div>
    {% endfor %}
</div>
{% endblock %}
```

### 6.7 Mise à jour du HomeController

```php
#[Route('/', name: 'app_home')]
public function index(RecetteRepository $recetteRepository): Response
{
    return $this->render('home/index.html.twig', [
        'dernieresRecettes' => $recetteRepository->findBy([], ['dateCreation' => 'DESC'], 6),
    ]);
}
```

### 6.8 Création du système de recherche

**Contrôleur `src/Controller/SearchController.php`** :
```php
<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use App\Repository\IngredientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/recherche', name: 'app_search')]
    public function search(
        Request $request, 
        IngredientRepository $ingredientRepository,
        RecetteRepository $recetteRepository
    ): Response {
        $query = $request->query->get('q', '');
        $recettes = [];

        if ($query) {
            // Recherche des ingrédients correspondants
            $ingredients = $ingredientRepository->findBySearchQuery($query);
            $ingredientIds = array_map(fn($ing) => $ing->getId(), $ingredients);
            
            // Recherche des recettes contenant ces ingrédients
            $recettes = $recetteRepository->findByIngredients($ingredientIds);
        }

        return $this->render('search/results.html.twig', [
            'query' => $query,
            'recettes' => $recettes,
        ]);
    }
}
```

### 6.9 Templates modernisés pour les CRUD

J'ai modernisé tous les templates avec Bootstrap :
- `templates/recette/index.html.twig`
- `templates/recette/show.html.twig`
- `templates/recette/edit.html.twig`
- `templates/ingredient/index.html.twig`
- `templates/categorie/index.html.twig`

### 6.10 Création de la page profil

**Contrôleur `src/Controller/ProfilController.php`** :
```php
<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use App\Repository\FavoriRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(
        RecetteRepository $recetteRepository,
        FavoriRepository $favoriRepository
    ): Response {
        $user = $this->getUser();

        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'mesRecettes' => $recetteRepository->findBy(['user' => $user]),
            'mesFavoris' => $favoriRepository->findBy(['user' => $user]),
        ]);
    }
}
```

### 6.11 Commit et fusion

```bash
git add .
git commit -m "feat: Templates Bootstrap complets avec navigation, hero, recherche et CRUD modernisés"
git checkout master
git merge feature/bootstrap-templates
git branch -d feature/bootstrap-templates
```

---

## ÉTAPE 9 : SYSTÈME DE FAVORIS

### 9.1 Nouvelle branche

```bash
git checkout -b feature/favoris
```

### 9.2 Création de l'entité Favori

```bash
php bin/console make:entity Favori
```

**Propriétés** :
- `user` (ManyToOne vers User)
- `recette` (ManyToOne vers Recette)
- `dateAjout` (datetime_immutable)

**Constructeur ajouté** :
```php
public function __construct()
{
    $this->dateAjout = new \DateTimeImmutable();
}
```

### 9.3 Migration de la table Favori

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### 9.4 Création du contrôleur API pour les favoris

**Fichier `src/Controller/Api/FavoriController.php`** :
```php
<?php

namespace App\Controller\Api;

use App\Entity\Favori;
use App\Repository\FavoriRepository;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/favori')]
#[IsGranted('ROLE_USER')]
class FavoriController extends AbstractController
{
    #[Route('/toggle/{id}', name: 'api_favori_toggle', methods: ['POST'])]
    public function toggle(
        int $id,
        RecetteRepository $recetteRepository,
        FavoriRepository $favoriRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        $recette = $recetteRepository->find($id);

        if (!$recette) {
            return $this->json(['error' => 'Recette non trouvée'], 404);
        }

        $favori = $favoriRepository->findOneBy([
            'user' => $user,
            'recette' => $recette
        ]);

        if ($favori) {
            // Supprimer le favori
            $em->remove($favori);
            $em->flush();
            return $this->json(['status' => 'removed', 'isFavorite' => false]);
        } else {
            // Ajouter aux favoris
            $favori = new Favori();
            $favori->setUser($user);
            $favori->setRecette($recette);
            $em->persist($favori);
            $em->flush();
            return $this->json(['status' => 'added', 'isFavorite' => true]);
        }
    }
}
```

### 9.5 Ajout du bouton favori dans les templates

**Dans `templates/recette/show.html.twig`** :
```twig
{% if app.user %}
    <button id="favoriBtn" 
            class="btn btn-outline-danger" 
            data-recette-id="{{ recette.id }}"
            data-is-favorite="{{ isFavorite ? 'true' : 'false' }}">
        <i class="bi bi-heart{{ isFavorite ? '-fill' : '' }}"></i>
        <span>{{ isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' }}</span>
    </button>
{% endif %}
```

### 9.6 JavaScript pour gérer les favoris

**Script ajouté dans `templates/recette/show.html.twig`** :
```javascript
<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoriBtn = document.getElementById('favoriBtn');
    
    if (favoriBtn) {
        favoriBtn.addEventListener('click', async function() {
            const recetteId = this.dataset.recetteId;
            
            try {
                const response = await fetch(`/api/favori/toggle/${recetteId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.isFavorite) {
                    this.classList.remove('btn-outline-danger');
                    this.classList.add('btn-danger');
                    this.querySelector('i').classList.add('bi-heart-fill');
                    this.querySelector('i').classList.remove('bi-heart');
                    this.querySelector('span').textContent = 'Retirer des favoris';
                } else {
                    this.classList.add('btn-outline-danger');
                    this.classList.remove('btn-danger');
                    this.querySelector('i').classList.remove('bi-heart-fill');
                    this.querySelector('i').classList.add('bi-heart');
                    this.querySelector('span').textContent = 'Ajouter aux favoris';
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Une erreur est survenue');
            }
        });
    }
});
</script>
```

### 9.7 Affichage des favoris dans le profil

J'ai mis à jour `templates/profil/index.html.twig` pour afficher les recettes favorites de l'utilisateur avec un système d'onglets.

### 9.8 Commit et fusion

```bash
git add .
git commit -m "feat: Système de favoris complet avec API AJAX et affichage dans profil"
git checkout master
git merge feature/favoris
git branch -d feature/favoris
```

---

## ÉTAPE 10 : UPLOAD D'IMAGES ET OPTIMISATIONS UI/UX

### 10.1 Nouvelle branche

```bash
git checkout -b feature/upload-images
```

### 10.2 Installation de VichUploaderBundle

```bash
composer require vich/uploader-bundle
```

### 10.3 Configuration de VichUploader

**Fichier `config/packages/vich_uploader.yaml`** :
```yaml
vich_uploader:
    db_driver: orm
    
    mappings:
        recette_images:
            uri_prefix: /uploads/recettes
            upload_destination: '%kernel.project_dir%/public/uploads/recettes'
            namer: Vich\UploaderBundle\Naming\SmartUniqueNamer
            
            inject_on_load: false
            delete_on_update: true
            delete_on_remove: true
```

### 10.4 Création du dossier uploads

```bash
mkdir -p public/uploads/recettes
chmod 755 public/uploads/recettes
```

### 10.5 Modification de l'entité Recette

**Ajout dans `src/Entity/Recette.php`** :
```php
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: RecetteRepository::class)]
#[Vich\Uploadable]
class Recette
{
    // ... propriétés existantes

    #[Vich\UploadableField(mapping: 'recette_images', fileNameProperty: 'image')]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
```

### 10.6 Mise à jour du formulaire Recette

**Dans `src/Form/RecetteType.php`** :
```php
use Vich\UploaderBundle\Form\Type\VichImageType;

public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('nom')
        ->add('description')
        ->add('imageFile', VichImageType::class, [
            'label' => 'Image de la recette',
            'required' => false,
            'allow_delete' => true,
            'delete_label' => 'Supprimer l\'image',
            'download_uri' => false,
            'image_uri' => false,
        ])
        // ... autres champs
    ;
}
```

### 10.7 Migration pour le champ updatedAt

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### 10.8 Optimisation de l'interface d'upload

Après avoir implémenté VichUploader, j'ai constaté plusieurs problèmes d'affichage que j'ai corrigés.

#### 10.8.1 Problème identifié : Formulaire d'édition trop grand

Le formulaire d'édition affichait une très grande image (500px+) générée automatiquement par VichUploader, ce qui obligeait à dézoomer à 25% pour voir le formulaire complet.

#### 10.8.2 Solution : Prévisualisation compacte

J'ai créé une miniature compacte de 80px × 120px avec feedback visuel pour remplacer la grande prévisualisation.

**Fichier `templates/recette/_form.html.twig` optimisé** :
```twig
{{ form_start(form, {'attr': {'enctype': 'multipart/form-data'}}) }}

<!-- Titre -->
<div class="mb-3">
    {{ form_label(form.nom, 'Titre', {'label_attr': {'class': 'form-label'}}) }}
    <span class="text-danger">*</span>
    {{ form_widget(form.nom, {'attr': {'class': 'form-control', 'placeholder': 'Omelette anti-gaspi'}}) }}
    {{ form_errors(form.nom) }}
</div>

<!-- Description -->
<div class="mb-3">
    {{ form_label(form.description, 'Description', {'label_attr': {'class': 'form-label'}}) }}
    <span class="text-danger">*</span>
    {{ form_widget(form.description, {'attr': {'class': 'form-control', 'rows': 4}}) }}
    {{ form_errors(form.description) }}
</div>

<!-- Upload image avec prévisualisation compacte -->
<div class="mb-3">
    {{ form_label(form.imageFile, 'Image de la recette', {'label_attr': {'class': 'form-label'}}) }}
    
    <!-- Prévisualisation compacte (80px) -->
    {% if recette.image %}
        <div class="d-flex align-items-center gap-3 p-3 mb-2 bg-light rounded border">
            <img src="/uploads/recettes/{{ recette.image }}" 
                 class="rounded shadow-sm" 
                 style="max-height: 80px; max-width: 120px; object-fit: cover;" 
                 alt="{{ recette.nom }}">
            <div class="flex-grow-1">
                <p class="mb-0 small text-success fw-bold">
                    <i class="bi bi-check-circle-fill"></i> Image actuelle
                </p>
                <p class="mb-0 small text-muted">
                    Pour la remplacer, sélectionnez une nouvelle image ci-dessous
                </p>
            </div>
        </div>
    {% endif %}
    
    <!-- Champ d'upload -->
    <div class="border rounded p-3 bg-white">
        {{ form_row(form.imageFile.file, {
            'label': false,
            'attr': {'class': 'form-control', 'accept': 'image/*'}
        }) }}
        
        {% if recette.image and form.imageFile.delete is defined %}
            <div class="form-check mt-2">
                {{ form_widget(form.imageFile.delete, {'attr': {'class': 'form-check-input'}}) }}
                {{ form_label(form.imageFile.delete, 'Supprimer l\'image actuelle', 
                    {'label_attr': {'class': 'form-check-label text-danger'}}) }}
            </div>
        {% endif %}
        
        {{ form_errors(form.imageFile) }}
        <small class="form-text text-muted mt-2 d-block">
            <i class="bi bi-info-circle"></i>
            Formats acceptés : JPG, PNG, GIF. Taille max : 5MB
        </small>
    </div>
</div>

<!-- Temps et personnes -->
<div class="row mb-3">
    <div class="col-md-6">
        {{ form_label(form.tempsCuisson, 'Temps de préparation', {'label_attr': {'class': 'form-label'}}) }}
        <div class="input-group">
            {{ form_widget(form.tempsCuisson, {'attr': {'class': 'form-control'}}) }}
            <span class="input-group-text">minutes</span>
        </div>
        {{ form_errors(form.tempsCuisson) }}
    </div>
    
    <div class="col-md-6">
        {{ form_label(form.nombrePersonnes, 'Nombre de personnes', {'label_attr': {'class': 'form-label'}}) }}
        {{ form_widget(form.nombrePersonnes, {'attr': {'class': 'form-control'}}) }}
        {{ form_errors(form.nombrePersonnes) }}
    </div>
</div>

<!-- Difficulté, Étapes, Catégorie, Boutons... -->
{{ form_end(form) }}
```

#### 10.8.3 Page edit.html.twig avec CSS/JS pour masquer doublons

**Fichier `templates/recette/edit.html.twig`** :
```twig
{% extends 'base.html.twig' %}

{% block title %}Modifier {{ recette.nom }} - Les Restes{% endblock %}

{% block body %}
<div class="container">
    <div class="mb-4">
        <a href="{{ path('app_profil') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour à mon profil
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h2 class="mb-4">Modifier ma recette</h2>
            
            {{ include('recette/_form.html.twig', {'button_label': 'Mettre à jour'}) }}
            
            <div class="mt-4 pt-3 border-top">
                <h6 class="text-muted">Actions</h6>
                <div class="d-flex gap-2">
                    <a href="{{ path('app_recette_show', {'id': recette.id}) }}" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> Aperçu
                    </a>
                    {{ include('recette/_delete_form.html.twig') }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Masquer la prévisualisation automatique de VichUploader */
    .vich-image,
    .vich-image img {
        display: none !important;
    }
    
    /* Afficher seulement notre miniature custom */
    .bg-light.rounded.border img {
        display: block !important;
    }
</style>

<script>
    // Supprimer les duplications VichUploader
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('form img[src*="/uploads/recettes/"]');
        images.forEach(function(img) {
            if (!img.closest('.bg-light')) {
                const parent = img.closest('.vich-image') || img.parentElement;
                if (parent && parent.tagName !== 'FORM') {
                    parent.remove();
                }
            }
        });
    });
</script>
{% endblock %}
```

#### 10.8.4 Uniformisation de l'affichage des images

J'ai corrigé tous les templates pour utiliser des chemins cohérents et uniformes :

**Avant** (incohérent) :
```twig
<img src="{{ recette.image ?: 'https://unsplash.com/...' }}">
<img src="uploads/recettes/{{ recette.image }}">  {# Manque le / #}
```

**Après** (uniforme) :
```twig
{% if recette.image %}
    <img src="/uploads/recettes/{{ recette.image }}" 
         style="height: 200px; object-fit: cover;" 
         alt="{{ recette.nom }}">
{% else %}
    <img src="https://source.unsplash.com/300x200/?food" 
         style="height: 200px; object-fit: cover;" 
         alt="{{ recette.nom }}">
{% endif %}
```

#### 10.8.5 Fichiers modifiés pour l'uniformisation

- ✅ `templates/home/index.html.twig`
- ✅ `templates/recette/index.html.twig`
- ✅ `templates/recette/show.html.twig`
- ✅ `templates/recette/edit.html.twig`
- ✅ `templates/recette/_form.html.twig`
- ✅ `templates/profil/index.html.twig`

#### 10.8.6 Bilan des améliorations

**Interface professionnelle** :
- ✅ Images uniformes (200px) avec `object-fit: cover`
- ✅ Prévisualisation compacte (80px) dans le formulaire
- ✅ Fallback Unsplash automatique
- ✅ Chemins d'images cohérents partout

**UX optimisée** :
- ✅ Page d'édition épurée (sans grosse image)
- ✅ Feedback visuel avec icônes Bootstrap
- ✅ Instructions claires pour l'utilisateur
- ✅ Suppression des duplications VichUploader

### 10.9 Tests complets de l'upload

J'ai testé toutes les fonctionnalités :
- ✅ Upload d'une image lors de la création d'une recette
- ✅ Affichage de l'image sur la page de détail (200px uniforme)
- ✅ Prévisualisation compacte dans le formulaire d'édition (80px)
- ✅ Modification/remplacement d'une image
- ✅ Suppression d'une image
- ✅ Images de fallback Unsplash si aucune image
- ✅ Formulaire utilisable à 100% de zoom (au lieu de 25%)

### 10.10 Commit complet avec toutes les améliorations

```bash
git add .
git commit -m "feat: Upload d'images complet avec interface optimisée

- Intégration VichUploaderBundle pour upload sécurisé
- Affichage uniforme des images (200px, object-fit: cover)
- Prévisualisation compacte dans formulaires (80px)
- Images fallback Unsplash automatiques
- Interface d'édition épurée et professionnelle
- CSS/JS pour masquer duplications VichUploader
- Chemins d'images cohérents sur toutes les pages
- Feedback visuel avec icônes Bootstrap
- Checkbox de suppression d'image
- Tests complets réussis"
```

### 10.11 Fusion et préparation de la prochaine fonctionnalité

```bash
git checkout master
git merge feature/upload-images
git branch -d feature/upload-images
git checkout -b feature/commentaires
```

---

## ÉTAPE 11 : SYSTÈME DE COMMENTAIRES 

### 11.1 Nouvelle branche

```bash
git checkout -b feature/commentaires
```

### 11.2 Création de l'entité Commentaire

```bash
php bin/console make:entity Commentaire
```

**Propriétés ajoutées** :
1. `contenu` (text, not null)
2. `note` (integer, not null) - Note de 1 à 5 étoiles
3. `dateCreation` (datetime_immutable, not null)

**Relations ajoutées** :
1. `user` (ManyToOne vers User, not null) - L'auteur du commentaire
2. `recette` (ManyToOne vers Recette, not null) - La recette commentée

### 11.3 Ajout du constructeur dans Commentaire

Dans `src/Entity/Commentaire.php`, j'ai ajouté le constructeur pour initialiser automatiquement la date :

```php
public function __construct()
{
    $this->dateCreation = new \DateTimeImmutable();
}
```

### 11.4 Mise à jour de l'entité User

J'ai ajouté la relation inverse dans `src/Entity/User.php` :

```php
/**
 * @var Collection<int, Commentaire>
 */
#[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'user')]
private Collection $commentaires;

// Dans le constructeur existant :
public function __construct()
{
    // ... code existant
    $this->commentaires = new ArrayCollection();
}

// Méthodes de gestion
public function getCommentaires(): Collection
{
    return $this->commentaires;
}

public function addCommentaire(Commentaire $commentaire): static
{
    if (!$this->commentaires->contains($commentaire)) {
        $this->commentaires->add($commentaire);
        $commentaire->setUser($this);
    }
    return $this;
}

public function removeCommentaire(Commentaire $commentaire): static
{
    if ($this->commentaires->removeElement($commentaire)) {
        if ($commentaire->getUser() === $this) {
            $commentaire->setUser(null);
        }
    }
    return $this;
}
```

### 11.5 Mise à jour de l'entité Recette

J'ai ajouté la relation inverse et une méthode pour calculer la moyenne des notes dans `src/Entity/Recette.php` :

```php
/**
 * @var Collection<int, Commentaire>
 */
#[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'recette')]
private Collection $commentaires;

// Dans le constructeur existant :
public function __construct()
{
    // ... code existant
    $this->commentaires = new ArrayCollection();
}

// Méthodes de gestion
public function getCommentaires(): Collection
{
    return $this->commentaires;
}

public function addCommentaire(Commentaire $commentaire): static
{
    if (!$this->commentaires->contains($commentaire)) {
        $this->commentaires->add($commentaire);
        $commentaire->setRecette($this);
    }
    return $this;
}

public function removeCommentaire(Commentaire $commentaire): static
{
    if ($this->commentaires->removeElement($commentaire)) {
        if ($commentaire->getRecette() === $this) {
            $commentaire->setRecette(null);
        }
    }
    return $this;
}

// Méthode utile pour calculer la moyenne des notes
public function getMoyenneNotes(): float
{
    $commentaires = $this->commentaires->toArray();
    if (empty($commentaires)) {
        return 0;
    }
    
    $totalNotes = array_sum(array_map(fn($c) => $c->getNote(), $commentaires));
    return round($totalNotes / count($commentaires), 1);
}
```

### 11.6 Migration de la table Commentaire

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### 11.7 Création du FormType pour les commentaires

J'ai créé `src/Form/CommentaireType.php` pour gérer le formulaire de commentaire avec notation :

```php
<?php

namespace App\Form;

use App\Entity\Commentaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('note', ChoiceType::class, [
                'choices' => [
                    '1 étoile' => 1,
                    '2 étoiles' => 2,
                    '3 étoiles' => 3,
                    '4 étoiles' => 4,
                    '5 étoiles' => 5,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Note',
                'attr' => ['class' => 'star-rating']
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Votre commentaire',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Partagez votre expérience...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
}
```

**Caractéristiques du formulaire** :
- Champ `note` : Choix de 1 à 5 étoiles avec radio buttons
- Champ `contenu` : Zone de texte pour le commentaire
- Labels et placeholders en français

### 11.8 Mise à jour du contrôleur RecetteController

J'ai modifié la méthode `show()` dans `src/Controller/RecetteController.php` pour gérer l'ajout de commentaires :

```php
use App\Entity\Commentaire;
use App\Form\CommentaireType;

#[Route('/{id}', name: 'app_recette_show', methods: ['GET', 'POST'])]
public function show(
    Request $request, 
    Recette $recette, 
    FavoriRepository $favoriRepository, 
    EntityManagerInterface $entityManager
): Response {
    // Augmenter le compteur de vues
    $recette->setVue($recette->getVue() + 1);
    $entityManager->flush();
    
    // Vérifier si la recette est en favori
    $isFavorite = false;
    if ($this->getUser()) {
        $isFavorite = $favoriRepository->findOneBy([
            'user' => $this->getUser(),
            'recette' => $recette
        ]) !== null;
    }

    // Formulaire de commentaire
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
```

**Fonctionnalités ajoutées** :
- Création du formulaire de commentaire
- Traitement de la soumission
- Association automatique de l'utilisateur et de la recette
- Message flash de confirmation
- Redirection pour éviter double soumission

### 11.9 Mise à jour du template show.html.twig

J'ai complètement refondu la section commentaires dans `templates/recette/show.html.twig`.

**Affichage de la note moyenne** :
```twig
<!-- Notes et avis (avec vraies données) -->
<div class="mb-3">
    {% if recette.commentaires|length > 0 %}
        {% set moyenne = recette.moyenneNotes %}
        <span class="me-2">
            {% for i in 1..5 %}
                {% if i <= moyenne %}
                    <i class="bi bi-star-fill text-warning"></i>
                {% else %}
                    <i class="bi bi-star text-muted"></i>
                {% endif %}
            {% endfor %}
        </span>
        <span class="text-muted">({{ recette.commentaires|length }} avis)</span>
        <span class="ms-2 fw-bold text-warning">{{ moyenne }}/5</span>
    {% else %}
        <span class="text-muted">Aucun avis pour le moment</span>
    {% endif %}
</div>
```

**Section complète des commentaires** :
```twig
<!-- Commentaires -->
<h4 class="mb-3">
    <i class="bi bi-chat-dots"></i> Commentaires ({{ recette.commentaires|length }})
</h4>

{% if app.user %}
    <!-- Formulaire d'avis -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="card-title">Laisser un avis</h6>
            {{ form_start(commentaireForm) }}
                {{ form_row(commentaireForm.note) }}
                {{ form_row(commentaireForm.contenu) }}
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-send"></i> Publier l'avis
                </button>
            {{ form_end(commentaireForm) }}
        </div>
    </div>
{% else %}
    <div class="alert alert-info">
        <a href="{{ path('app_login') }}">Connectez-vous</a> pour laisser un commentaire.
    </div>
{% endif %}

<!-- Liste des commentaires -->
{% if recette.commentaires|length > 0 %}
    {% for commentaire in recette.commentaires %}
        <div class="border-start border-3 border-success ps-3 mb-3">
            <div class="d-flex justify-content-between">
                <strong>{{ commentaire.user.prenom }} {{ commentaire.user.nom }}</strong>
                <small class="text-muted">{{ commentaire.dateCreation|date('d M Y') }}</small>
            </div>
            <div class="mb-1">
                {% for i in 1..5 %}
                    {% if i <= commentaire.note %}
                        <i class="bi bi-star-fill text-warning"></i>
                    {% else %}
                        <i class="bi bi-star text-muted"></i>
                    {% endif %}
                {% endfor %}
            </div>
            <p class="mb-0">{{ commentaire.contenu }}</p>
        </div>
    {% endfor %}
{% else %}
    <p class="text-muted">Aucun commentaire pour le moment. Soyez le premier à donner votre avis !</p>
{% endif %}
```

**Fonctionnalités du template** :
- Affichage de la moyenne des notes avec étoiles
- Formulaire de commentaire (si connecté)
- Message d'invitation à se connecter (si déconnecté)
- Liste de tous les commentaires avec :
  - Nom de l'auteur
  - Date de publication
  - Note en étoiles
  - Contenu du commentaire
- Message si aucun commentaire

### 11.10 Tests du système de commentaires

J'ai testé toutes les fonctionnalités :
- ✅ Affichage du formulaire pour utilisateurs connectés
- ✅ Message de connexion pour utilisateurs non connectés
- ✅ Soumission d'un commentaire avec note
- ✅ Affichage des commentaires avec étoiles
- ✅ Calcul et affichage de la moyenne des notes
- ✅ Compteur de commentaires mis à jour
- ✅ Message flash de confirmation

### 11.11 Commit du système de commentaires complet

```bash
git add .
git commit -m "feat: Système de commentaires et notation complet

- Entité Commentaire avec relations User et Recette
- FormType avec choix de note (1-5 étoiles) et commentaire
- Intégration dans RecetteController show()
- Affichage des commentaires avec étoiles Bootstrap Icons
- Calcul automatique de la moyenne des notes
- Interface utilisateur moderne et responsive
- Message flash de confirmation
- Protection connexion utilisateur
- Tests complets réussis"
```

### 11.12 Amélioration des étoiles cliquables

Après les premiers tests, j'ai constaté que les étoiles ne restaient pas sélectionnées après le clic. Le problème venait du CSS avec `flex-direction: row-reverse`.

**Correction du CSS et JavaScript** :
```css
.star-rating {
    display: flex;
    justify-content: flex-start;
}

.star-input {
    display: none;
}

.star-label {
    cursor: pointer;
    font-size: 1.5rem;
    color: #ddd;
    transition: color 0.2s;
    margin: 0 2px;
}

.star-label.active {
    color: #ffc107;
}
```

**JavaScript amélioré** :
```javascript
document.addEventListener('DOMContentLoaded', function() {
    const starInputs = document.querySelectorAll('.star-input');
    const starLabels = document.querySelectorAll('.star-label');
    
    function updateStars(rating) {
        starLabels.forEach((label, index) => {
            if (index < rating) {
                label.classList.add('active');
            } else {
                label.classList.remove('active');
            }
        });
    }
    
    starLabels.forEach((label, index) => {
        label.addEventListener('mouseenter', function() {
            updateStars(index + 1);
        });
        
        label.addEventListener('click', function() {
            const rating = index + 1;
            starInputs[index].checked = true;
            
            const hiddenInputs = document.querySelectorAll('input[name="commentaire[note]"]:not(.star-input)');
            hiddenInputs.forEach(input => {
                input.checked = (input.value == rating);
            });
            
            updateStars(rating);
            label.closest('.star-rating').setAttribute('data-rating', rating);
        });
    });
    
    document.querySelector('.star-rating').addEventListener('mouseleave', function() {
        const currentRating = this.getAttribute('data-rating');
        if (currentRating) {
            updateStars(parseInt(currentRating));
        } else {
            updateStars(0);
        }
    });
});
```

**Fonctionnalités** :
- ✅ Survol : étoiles dorées temporaires
- ✅ Clic : sélection persistante
- ✅ Synchronisation avec Symfony
- ✅ Retour à la sélection après survol

### 11.13 Ajout de la suppression de commentaires

Pour améliorer l'expérience utilisateur, j'ai ajouté la possibilité de supprimer ses propres commentaires.

**Contrôleur `src/Controller/CommentaireController.php`** :
```php
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
            throw $this->createAccessDeniedException(
                'Vous ne pouvez supprimer que vos propres commentaires.'
            );
        }

        $recetteId = $commentaire->getRecette()->getId();
        
        $entityManager->remove($commentaire);
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire supprimé avec succès.');
        
        return $this->redirectToRoute('app_recette_show', ['id' => $recetteId]);
    }
}
```

**Sécurité** :
- Vérification que l'utilisateur est l'auteur du commentaire
- Exception levée si tentative de suppression non autorisée
- Message flash de confirmation

### 11.14 Bouton de suppression dans le template

J'ai ajouté un menu déroulant avec option de suppression pour les commentaires de l'utilisateur :

```twig
<!-- Liste des commentaires -->
{% if recette.commentaires|length > 0 %}
    {% for commentaire in recette.commentaires %}
        <div class="border-start border-3 border-success ps-3 mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>{{ commentaire.user.prenom }} {{ commentaire.user.nom }}</strong>
                    <small class="text-muted ms-2">{{ commentaire.dateCreation|date('d M Y') }}</small>
                </div>
                
                <!-- Bouton supprimer si c'est son propre commentaire -->
                {% if app.user and commentaire.user == app.user %}
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <button class="dropdown-item text-danger" 
                                        onclick="confirmerSuppressionCommentaire({{ commentaire.id }})">
                                    <i class="bi bi-trash"></i> Supprimer
                                </button>
                            </li>
                        </ul>
                    </div>
                {% endif %}
            </div>
            
            <div class="mb-1">
                {% for i in 1..5 %}
                    {% if i <= commentaire.note %}
                        <i class="bi bi-star-fill text-warning"></i>
                    {% else %}
                        <i class="bi bi-star text-muted"></i>
                    {% endif %}
                {% endfor %}
            </div>
            <p class="mb-0">{{ commentaire.contenu }}</p>
        </div>
    {% endfor %}
{% else %}
    <p class="text-muted">Aucun commentaire pour le moment. Soyez le premier à donner votre avis !</p>
{% endif %}
```

### 11.15 Modal de confirmation de suppression

J'ai ajouté un modal Bootstrap pour confirmer la suppression :

```twig
<!-- Modal de confirmation suppression commentaire -->
<div class="modal fade" id="deleteCommentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Supprimer le commentaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce commentaire ?</p>
                <p class="text-warning small">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <form method="post" id="deleteCommentForm" style="display: inline;">
                    <input type="hidden" name="_token" value="{{ csrf_token('delete') }}">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
```

**JavaScript pour le modal** :
```javascript
function confirmerSuppressionCommentaire(commentaireId) {
    document.getElementById('deleteCommentForm').action = '/commentaire/' + commentaireId + '/delete';
    new bootstrap.Modal(document.getElementById('deleteCommentModal')).show();
}
```

### 11.16 Tests complets du système de commentaires

J'ai testé toutes les fonctionnalités finales :
- ✅ Formulaire avec étoiles cliquables fonctionnelles
- ✅ Sélection des étoiles persistante après clic
- ✅ Soumission du commentaire avec note
- ✅ Affichage des commentaires avec design moderne
- ✅ Bouton de suppression visible uniquement pour l'auteur
- ✅ Modal de confirmation avant suppression
- ✅ Redirection et message flash après suppression
- ✅ Calcul de la moyenne mis à jour automatiquement

### 11.17 Commit final du système de commentaires

```bash
git add .
git commit -m "feat: Système de commentaires complet avec notes

- Entité Commentaire avec notes 1-5 étoiles
- Formulaire avec étoiles cliquables Bootstrap
- Calcul automatique moyenne des notes
- Affichage commentaires avec vraies données
- Suppression commentaires (auteur seulement)
- Modal de confirmation suppression
- Interface responsive et moderne
- Tests complets réussis"
```

### 11.18 Fusion de la branche commentaires

```bash
git checkout master
git merge feature/commentaires
git branch -d feature/commentaires
```

---

## ÉTAPE 12 : AMÉLIORATIONS UX 💫

### 12.1 Nouvelle branche

```bash
git checkout -b feature/ameliorations-ux
```

### 12.2 Plan des améliorations UX

J'ai identifié trois priorités pour améliorer l'expérience utilisateur :

**Priorité 1 : Gestion dynamique des ingrédients** 
- Formulaire recette avec ajout/suppression d'ingrédients en temps réel
- Autocomplete sur les noms d'ingrédients existants
- Validation côté client
- Interface intuitive avec boutons +/-

**Priorité 2 : Recherche avancée** 
- Filtres par catégorie, difficulté, temps de préparation
- Tri par note, date, popularité
- Recherche textuelle améliorée
- Affichage des résultats optimisé

**Priorité 3 : Interface enrichie** ✨
- Pagination élégante pour les listes
- Loading states pour les actions AJAX
- Animations CSS subtiles
- Messages de feedback améliorés

### 12.3 Création de l'API pour l'autocomplete des ingrédients

J'ai créé `src/Controller/Api/IngredientController.php` pour fournir une API de recherche :

```php
<?php

namespace App\Controller\Api;

use App\Repository\IngredientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/ingredients')]
class IngredientController extends AbstractController
{
    #[Route('/search', name: 'api_ingredients_search', methods: ['GET'])]
    public function search(Request $request, IngredientRepository $ingredientRepository): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        if (strlen($query) < 2) {
            return new JsonResponse([]);
        }
        
        $ingredients = $ingredientRepository->createQueryBuilder('i')
            ->where('i.nom LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
        
        $data = [];
        foreach ($ingredients as $ingredient) {
            $data[] = [
                'id' => $ingredient->getId(),
                'nom' => $ingredient->getNom(),
                'unite' => $ingredient->getUnite()
            ];
        }
        
        return new JsonResponse($data);
    }
}
```

**Fonctionnalités de l'API** :
- Recherche d'ingrédients par nom (minimum 2 caractères)
- Limite à 10 résultats pour performance
- Retourne ID, nom et unité par défaut
- Format JSON pour intégration JavaScript

### 12.4 Création du FormType RecetteIngredient

J'ai créé `src/Form/RecetteIngredientType.php` pour gérer chaque ligne d'ingrédient :

```php
<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\RecetteIngredient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecetteIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ingredient', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un ingrédient...',
                'attr' => [
                    'class' => 'ingredient-select',
                    'data-autocomplete' => 'true'
                ]
            ])
            ->add('quantite', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 250'
                ]
            ])
            ->add('unite', ChoiceType::class, [
                'choices' => [
                    'Unité(s)' => 'unité(s)',
                    'Gramme(s)' => 'g',
                    'Kilogramme(s)' => 'kg',
                    'Millilitre(s)' => 'ml',
                    'Litre(s)' => 'l',
                    'Cuillère(s) à café' => 'c.à.c',
                    'Cuillère(s) à soupe' => 'c.à.s',
                    'Tasse(s)' => 'tasse(s)',
                    'Pincée(s)' => 'pincée(s)'
                ],
                'placeholder' => 'Unité',
                'attr' => ['class' => 'form-select']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecetteIngredient::class,
        ]);
    }
}
```

**Champs du formulaire** :
- `ingredient` : Sélection avec autocomplete
- `quantite` : Champ texte pour la quantité
- `unite` : Liste déroulante avec unités courantes

### 12.5 Modification du RecetteType principal

J'ai modifié `src/Form/RecetteType.php` pour intégrer la collection d'ingrédients :

```php
<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Recette;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description', TextareaType::class)
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image de la recette',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer l\'image',
                'download_uri' => false,
                'image_uri' => true,
                'asset_helper' => true,
            ])
            ->add('tempsCuisson', IntegerType::class)
            ->add('nombrePersonnes', IntegerType::class)
            ->add('difficulte', IntegerType::class)
            ->add('etapes', TextareaType::class)
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
            ])
            ->add('recetteIngredients', CollectionType::class, [
                'entry_type' => RecetteIngredientType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'attr' => ['class' => 'ingredients-collection']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}
```

**Options importantes de la collection** :
- `allow_add` : Permet d'ajouter des ingrédients dynamiquement
- `allow_delete` : Permet de supprimer des ingrédients
- `by_reference` : false pour gérer correctement les relations
- `prototype` : true pour générer le template JavaScript

### 12.6 Mise à jour du template new.html.twig

J'ai remplacé la section ingrédients statique par le système dynamique dans `templates/recette/new.html.twig` :

**Ancien code (statique) - SUPPRIMÉ** :
```twig
<!-- Ingrédients -->
<h5 class="mb-3">Ingrédients</h5>
<div id="ingredients-section" class="mb-3">
    <!-- HTML statique fake -->
</div>
```

**Nouveau code (dynamique)** :
```twig
<!-- Ingrédients dynamiques -->
<h5 class="mb-3">Ingrédients</h5>
<div id="ingredients-collection" class="mb-3" 
     data-prototype="{{ form_widget(form.recetteIngredients.vars.prototype)|e('html_attr') }}">
    
    {% for recetteIngredient in form.recetteIngredients %}
        <div class="ingredient-row border rounded p-2 mb-2">
            <div class="row">
                <div class="col-md-4">
                    {{ form_widget(recetteIngredient.ingredient, {'attr': {'class': 'form-select form-select-sm'}}) }}
                </div>
                <div class="col-md-3">
                    {{ form_widget(recetteIngredient.quantite, {'attr': {'class': 'form-control form-control-sm'}}) }}
                </div>
                <div class="col-md-3">
                    {{ form_widget(recetteIngredient.unite, {'attr': {'class': 'form-select form-select-sm'}}) }}
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-ingredient">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        </div>
    {% endfor %}
</div>

<button type="button" class="btn btn-outline-success btn-sm mb-4" id="add-ingredient">
    <i class="bi bi-plus"></i> Ajouter un ingrédient
</button>
```

### 12.7 Problème rencontré : Erreur Symfony

Lors du premier test, j'ai rencontré cette erreur :
```
RuntimeError: Neither the property "recetteIngredients" nor one of the methods 
"recetteIngredients()", "getrecetteIngredients()" exist in class "FormView"
```

**Cause** : Le champ `recetteIngredients` n'existait pas encore dans le `RecetteType`.

**Solution** : Ajout de la collection dans le `RecetteType` (étape 12.5).

### 12.8 Clear cache et vérification

```bash
php bin/console cache:clear
```

Après le clear cache, le formulaire fonctionnait mais avec un problème d'affichage.

### 12.9 Problème d'affichage des champs dynamiques

Lors de l'ajout d'un ingrédient, les champs apparaissaient sans style Bootstrap :
- Les `<select>` et `<input>` s'affichaient en HTML brut
- Pas de classes CSS Bootstrap
- Layout cassé

**Cause** : Le JavaScript initial utilisait des regex complexes pour parser le prototype Symfony, ce qui ne fonctionnait pas correctement.

**Solution** : JavaScript complètement revu pour injecter proprement le HTML.

### 12.10 JavaScript final pour gestion dynamique

J'ai créé un JavaScript robuste dans `templates/recette/new.html.twig` :

```javascript
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ingredientsContainer = document.getElementById('ingredients-collection');
    const addButton = document.getElementById('add-ingredient');
    let index = ingredientsContainer.children.length;
    
    // Fonction pour ajouter un nouvel ingrédient
    addButton.addEventListener('click', function() {
        const prototype = ingredientsContainer.dataset.prototype;
        const newForm = prototype.replace(/__name__/g, index);
        
        // Créer le wrapper avec le bon styling
        const wrapper = document.createElement('div');
        wrapper.className = 'ingredient-row border rounded p-2 mb-2';
        
        // Créer la structure row/col
        wrapper.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <div class="ingredient-field"></div>
                </div>
                <div class="col-md-3">
                    <div class="quantite-field"></div>
                </div>
                <div class="col-md-3">
                    <div class="unite-field"></div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-ingredient">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        `;
        
        // Injecter le HTML du prototype
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = newForm;
        
        // Récupérer et styliser les champs
        const ingredientSelect = tempDiv.querySelector('select[name*="ingredient"]');
        const quantiteInput = tempDiv.querySelector('input[name*="quantite"]');
        const uniteSelect = tempDiv.querySelector('select[name*="unite"]');
        
        if (ingredientSelect) {
            ingredientSelect.className = 'form-select form-select-sm';
            wrapper.querySelector('.ingredient-field').appendChild(ingredientSelect);
        }
        
        if (quantiteInput) {
            quantiteInput.className = 'form-control form-control-sm';
            wrapper.querySelector('.quantite-field').appendChild(quantiteInput);
        }
        
        if (uniteSelect) {
            uniteSelect.className = 'form-select form-select-sm';
            wrapper.querySelector('.unite-field').appendChild(uniteSelect);
        }
        
        // Ajouter au container
        ingredientsContainer.appendChild(wrapper);
        index++;
        
        // Ajouter l'événement de suppression
        wrapper.querySelector('.remove-ingredient').addEventListener('click', function() {
            wrapper.remove();
        });
    });
    
    // Gestion de la suppression pour les éléments existants
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-ingredient')) {
            e.target.closest('.ingredient-row').remove();
        }
    });
    
    // Styliser les éléments existants au chargement
    document.querySelectorAll('.ingredient-row select, .ingredient-row input').forEach(field => {
        if (field.tagName === 'SELECT') {
            field.className = 'form-select form-select-sm';
        } else {
            field.className = 'form-control form-control-sm';
        }
    });
});
</script>
```

**Fonctionnalités du JavaScript** :
- Récupération du prototype Symfony
- Remplacement du placeholder `__name__` par l'index
- Injection dans une structure HTML Bootstrap
- Stylisation automatique des champs
- Gestion de la suppression
- Stylisation des éléments au chargement

### 12.11 CSS pour améliorer l'affichage

J'ai ajouté du CSS dans le template pour une meilleure UX :

```css
<style>
.ingredient-row .row > div {
    padding-left: 5px;
    padding-right: 5px;
}

.ingredient-row {
    background-color: #f8f9fa;
    transition: all 0.2s ease;
}

.ingredient-row:hover {
    background-color: #e9ecef;
}

.remove-ingredient {
    width: 100%;
}
</style>
```

**Effets visuels** :
- Fond gris clair pour chaque ligne
- Effet hover pour feedback visuel
- Espacement optimisé entre les colonnes
- Bouton de suppression pleine largeur

### 12.12 Tests de la gestion dynamique

J'ai testé toutes les fonctionnalités :
- ✅ Affichage du formulaire sans erreur
- ✅ Ajout d'une ligne d'ingrédient avec le bouton +
- ✅ Suppression d'une ligne avec le bouton X
- ✅ Style Bootstrap correctement appliqué
- ✅ Formulaire soumis avec les ingrédients
- ✅ Données enregistrées en base de données

---

## PROCHAINES ÉTAPES

### Fonctionnalités à développer

####  Gestion des ingrédients dans les recettes
- [ ] Formulaire dynamique pour ajouter plusieurs ingrédients
- [ ] Interface drag & drop pour réorganiser les ingrédients
- [ ] Autocomplete pour rechercher des ingrédients existants
- [ ] Affichage structuré des ingrédients avec quantités

####  Recherche avancée
- [ ] Ajout note moyenne dans Recette
- [ ] Entité Note (user, recette, valeur)
- [ ] Affichage étoiles
- [ ] Calcul note moyenne

####  Recherche avancée
- [ ] Filtres par catégorie
- [ ] Filtres par difficulté
- [ ] Filtres par temps de cuisson
- [ ] Tri des résultats

####  Profil utilisateur avancé
- [ ] Modification avatar
- [ ] Modification bio
- [ ] Statistiques personnelles
- [ ] Badge et gamification

####  API REST
- [ ] Endpoints API pour recettes
- [ ] Documentation API (OpenAPI)
- [ ] Authentification JWT
- [ ] Rate limiting

####  Tests
- [ ] Tests unitaires (PHPUnit)
- [ ] Tests fonctionnels
- [ ] Tests d'intégration
- [ ] Couverture de code >70%

####  Performance
- [ ] Cache Symfony
- [ ] Optimisation requêtes Doctrine
- [ ] Lazy loading images
- [ ] Pagination

####  Sécurité
- [ ] CSRF tokens partout
- [ ] Validation stricte formulaires
- [ ] Rate limiting connexion
- [ ] Audit sécurité

### Améliorations techniques

- [ ] Migration vers Symfony 7.4 (si nouvelle version)
- [ ] Mise en place CI/CD (GitHub Actions)
- [ ] Docker Compose complet (Nginx, PHP, MySQL)
- [ ] Documentation développeur (README détaillé)

---

## CONCLUSION

### État actuel du projet

Le projet Les Restes est actuellement dans un état fonctionnel avec les fonctionnalités suivantes opérationnelles :

✅ **Backend complet** :
- Architecture Symfony 7.4 solide
- Base de données MySQL bien structurée
- Entités avec relations complexes (User, Recette, Ingredient, Categorie, Commentaire, Favori)
- Système d'authentification sécurisé

✅ **Fonctionnalités utilisateur** :
- Inscription et connexion
- Création et gestion de recettes
- Upload d'images optimisé
- Système de favoris avec AJAX
- **Système de commentaires et notation complet** (étoiles cliquables + suppression)
- Recherche par ingrédients
- Profil utilisateur avec onglets

 **En cours de développement** :
- Gestion dynamique des ingrédients dans les recettes (Étape 12)
- Autocomplete pour rechercher des ingrédients
- Formulaire avec ajout/suppression de lignes

✅ **Interface utilisateur** :
- Design moderne avec Bootstrap 5
- Navigation responsive
- Templates cohérents et professionnels
- Images uniformes avec fallback
- Formulaires optimisés

### Points forts du projet

1. **Architecture propre** : Respect des standards Symfony et des bonnes pratiques
2. **Code versionné** : Utilisation méthodique de Git avec branches thématiques
3. **Design soigné** : Interface moderne et intuitive
4. **Fonctionnalités AJAX** : Favoris sans rechargement de page
5. **Optimisations UX** : Formulaires compacts, images uniformes

### Compétences démontrées

- ✅ Maîtrise de Symfony 7.4
- ✅ Gestion de base de données avec Doctrine
- ✅ Sécurité et authentification
- ✅ Upload de fichiers avec VichUploader
- ✅ Frontend moderne (Bootstrap, JavaScript)
- ✅ API REST (favoris)
- ✅ Git et méthodologie de développement

### Préparation pour la soutenance

Ce projet démontre ma capacité à :
- Concevoir une application web complète
- Utiliser un framework PHP moderne
- Créer une interface utilisateur professionnelle
- Gérer un projet avec Git
- Documenter mon travail de manière détaillée

---

**Dernière mise à jour** : Novembre 2025  
**Statut** : En développement actif  
**Graduation prévue** : Avril 2026

---

*Documentation rédigée dans le cadre du Titre Professionnel DWWM - Dawan Toulouse*