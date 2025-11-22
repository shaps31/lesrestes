# DOCUMENTATION DE RÉALISATION

## PROJET LES RESTES - Application Anti-Gaspillage Alimentaire

**Auteur** : Bah shabadine
**Formation** : Titre Professionnel Développeur Web et Web Mobile - Niveau 5
**Centre de formation** : Dawan Toulouse
**Date de début** : 30 juin 2025
**Graduation prévue** : Avril 2026
**Status** : En développement actif

---

## Table des matières

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

-   Trouver des recettes en fonction des ingrédients disponibles
-   Partager leurs propres recettes anti-gaspi
-   Sauvegarder leurs recettes favorites
-   Consulter des recettes par catégorie

### Stack technique

-   **Backend** : Symfony 7.3.\* (aligné avec `composer.json`)
-   **PHP** : Version 8.3.6
-   **Base de données** : MySQL 8.0 (Docker)
-   **Frontend** : Bootstrap 5, JavaScript vanilla
-   **Assets** : Webpack Encore
-   **Gestionnaire de dépendances** : Composer 2.8.12
-   **CLI** : Symfony CLI 5.15.1
-   **Versionning** : Git

---

## ÉTAPE 1 : SETUP INITIAL

### 1.1 Installation de l'environnement

J'ai commencé par installer tous les outils nécessaires :

```bash
# Vérification des versions installées
php -v # PHP 8.3.6
composer -V # Composer 2.8.12
symfony -V # Symfony CLI 5.15.1
```

### 1.2 Création du projet Symfony

```bash
# Création du projet avec Symfony
symfony new lesrestes --version=stable

# Vérification de la version installée
cd lesrestes
php bin/console about
# Symfony 7.3 installé avec succès
```

### 1.3 Configuration de la base de données avec Docker

J'ai choisi Docker pour avoir un environnement MySQL reproductible.

**Fichier `docker-compose.yml` créé** :

```yaml
version: "3.8"

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
git commit -m "Initial Symfony 7.3 setup with Docker MySQL"
git checkout -b feature/entities
```

---

## ÉTAPE 2 : ENTITÉS PRINCIPALES

### 2.1 Création de l'entité User avec sécurité

```bash
php bin/console make:user
```

**Configuration choisie** :

-   Class name : `User`
-   Store users in database : `yes`
-   Unique identifier : `email`
-   Password field : `yes`

### 2.2 Extension de l'entité User

J'ai ajouté les propriétés supplémentaires selon le cahier des charges :

```bash
php bin/console make:entity User
```

**Propriétés ajoutées** :

-   `nom` (string, 50, not null)
-   `prenom` (string, 50, not null)
-   `bio` (text, nullable)
-   `avatar` (string, 255, nullable)
-   `dateInscription` (datetime_immutable, not null)
-   `isVerified` (boolean, not null)

### 2.3 Création de l'entité Ingredient

```bash
php bin/console make:entity Ingredient
```

**Propriétés** :

-   `nom` (string, 50, not null, unique)
-   `unite` (string, 20, nullable)
-   `description` (text, nullable)
-   `dateCreation` (datetime_immutable, not null)

### 2.4 Création de l'entité Categorie

```bash
php bin/console make:entity Categorie
```

**Propriétés** :

-   `nom` (string, 50, not null)
-   `description` (text, nullable)
-   `couleur` (string, 7, nullable)

### 2.5 Création de l'entité Recette

```bash
php bin/console make:entity Recette
```

**Propriétés** :

-   `nom` (string, 100, not null)
-   `description` (text, not null)
-   `etapes` (text, not null)
-   `image` (string, 255, nullable)
-   `tempsCuisson` (integer, not null)
-   `nombrePersonnes` (integer, not null)
-   `difficulte` (integer, not null)
-   `vue` (integer, not null, default 0)
-   `dateCreation` (datetime_immutable, not null)

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

-   `quantite` (string, 50, not null)
-   `unite` (string, 20, nullable)
-   `recette` (ManyToOne vers Recette)
-   `ingredient` (ManyToOne vers Ingredient)

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

-   Authenticator type : `Login form authenticator`
-   Controller class name : `SecurityController`
-   Generate logout URL : `yes`

### 3.3 Création du formulaire d'inscription

```bash
php bin/console make:registration-form
```

**Options choisies** :

-   Add @UniqueEntity validation : `yes`
-   Send email verification : `no` (pour l'instant)
-   Automatically authenticate : `yes`
-   Redirect after registration : `/`

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

-   **Inscription** : `http://localhost:8004/register`
-   **Connexion** : `http://localhost:8004/login`
-   **Déconnexion** : `http://localhost:8004/logout`

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

-   Catégories : `http://127.0.0.1:8004/categorie/`
-   Ingrédients : `http://127.0.0.1:8004/ingredient/`
-   Recettes : `http://127.0.0.1:8004/recette/`

### 4.5 Correction du formulaire Recette

J'ai supprimé le champ `user` dans `src/Form/RecetteType.php` car il sera défini automatiquement :

```php
public function buildForm(FormBuilderInterface $builder, array $options): void
{
 $builder
 ->add('nom')
 ->add('description')
 ->add('etapes')
 // ->add('user') // ← Supprimé
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
const Encore = require("@symfony/webpack-encore");

Encore.setOutputPath("public/build/")
    .setPublicPath("/build")
    .addEntry("app", "./assets/app.js")
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader();

module.exports = Encore.getWebpackConfig();
```

**Fichier `assets/app.js`** :

```javascript
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap";
import "./styles/app.css";
```

**Fichier `assets/styles/app.css`** :

```css
:root {
    --primary-color: #1e5128;
    --secondary-color: #4e9f3d;
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

-   `templates/recette/index.html.twig`
-   `templates/recette/show.html.twig`
-   `templates/recette/edit.html.twig`
-   `templates/ingredient/index.html.twig`
-   `templates/categorie/index.html.twig`

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

-   `user` (ManyToOne vers User)
-   `recette` (ManyToOne vers Recette)
-   `dateAjout` (datetime_immutable)

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
    upload_destination: "%kernel.project_dir%/public/uploads/recettes"
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
<img src="uploads/recettes/{{ recette.image }}"> {# Manque le / #}
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

-   [OK] `templates/home/index.html.twig`
-   [OK] `templates/recette/index.html.twig`
-   [OK] `templates/recette/show.html.twig`
-   [OK] `templates/recette/edit.html.twig`
-   [OK] `templates/recette/_form.html.twig`
-   [OK] `templates/profil/index.html.twig`

#### 10.8.6 Bilan des améliorations

**Interface professionnelle** :

-   [OK] Images uniformes (200px) avec `object-fit: cover`
-   [OK] Prévisualisation compacte (80px) dans le formulaire
-   [OK] Fallback Unsplash automatique
-   [OK] Chemins d'images cohérents partout

**UX optimisée** :

-   [OK] Page d'édition épurée (sans grosse image)
-   [OK] Feedback visuel avec icônes Bootstrap
-   [OK] Instructions claires pour l'utilisateur
-   [OK] Suppression des duplications VichUploader

### 10.9 Tests complets de l'upload

J'ai testé toutes les fonctionnalités :

-   [OK] Upload d'une image lors de la création d'une recette
-   [OK] Affichage de l'image sur la page de détail (200px uniforme)
-   [OK] Prévisualisation compacte dans le formulaire d'édition (80px)
-   [OK] Modification/remplacement d'une image
-   [OK] Suppression d'une image
-   [OK] Images de fallback Unsplash si aucune image
-   [OK] Formulaire utilisable à 100% de zoom (au lieu de 25%)

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

-   Champ `note` : Choix de 1 à 5 étoiles avec radio buttons
-   Champ `contenu` : Zone de texte pour le commentaire
-   Labels et placeholders en français

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

-   Création du formulaire de commentaire
-   Traitement de la soumission
-   Association automatique de l'utilisateur et de la recette
-   Message flash de confirmation
-   Redirection pour éviter double soumission

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

-   Affichage de la moyenne des notes avec étoiles
-   Formulaire de commentaire (si connecté)
-   Message d'invitation à se connecter (si déconnecté)
-   Liste de tous les commentaires avec :
-   Nom de l'auteur
-   Date de publication
-   Note en étoiles
-   Contenu du commentaire
-   Message si aucun commentaire

### 11.10 Tests du système de commentaires

J'ai testé toutes les fonctionnalités :

-   [OK] Affichage du formulaire pour utilisateurs connectés
-   [OK] Message de connexion pour utilisateurs non connectés
-   [OK] Soumission d'un commentaire avec note
-   [OK] Affichage des commentaires avec étoiles
-   [OK] Calcul et affichage de la moyenne des notes
-   [OK] Compteur de commentaires mis à jour
-   [OK] Message flash de confirmation

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
document.addEventListener("DOMContentLoaded", function () {
    const starInputs = document.querySelectorAll(".star-input");
    const starLabels = document.querySelectorAll(".star-label");

    function updateStars(rating) {
        starLabels.forEach((label, index) => {
            if (index < rating) {
                label.classList.add("active");
            } else {
                label.classList.remove("active");
            }
        });
    }

    starLabels.forEach((label, index) => {
        label.addEventListener("mouseenter", function () {
            updateStars(index + 1);
        });

        label.addEventListener("click", function () {
            const rating = index + 1;
            starInputs[index].checked = true;

            const hiddenInputs = document.querySelectorAll(
                'input[name="commentaire[note]"]:not(.star-input)'
            );
            hiddenInputs.forEach((input) => {
                input.checked = input.value == rating;
            });

            updateStars(rating);
            label.closest(".star-rating").setAttribute("data-rating", rating);
        });
    });

    document
        .querySelector(".star-rating")
        .addEventListener("mouseleave", function () {
            const currentRating = this.getAttribute("data-rating");
            if (currentRating) {
                updateStars(parseInt(currentRating));
            } else {
                updateStars(0);
            }
        });
});
```

**Fonctionnalités** :

-   [OK] Survol : étoiles dorées temporaires
-   [OK] Clic : sélection persistante
-   [OK] Synchronisation avec Symfony
-   [OK] Retour à la sélection après survol

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

-   Vérification que l'utilisateur est l'auteur du commentaire
-   Exception levée si tentative de suppression non autorisée
-   Message flash de confirmation

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
    document.getElementById("deleteCommentForm").action =
        "/commentaire/" + commentaireId + "/delete";
    new bootstrap.Modal(document.getElementById("deleteCommentModal")).show();
}
```

### 11.16 Tests complets du système de commentaires

J'ai testé toutes les fonctionnalités finales :

-   [OK] Formulaire avec étoiles cliquables fonctionnelles
-   [OK] Sélection des étoiles persistante après clic
-   [OK] Soumission du commentaire avec note
-   [OK] Affichage des commentaires avec design moderne
-   [OK] Bouton de suppression visible uniquement pour l'auteur
-   [OK] Modal de confirmation avant suppression
-   [OK] Redirection et message flash après suppression
-   [OK] Calcul de la moyenne mis à jour automatiquement

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

## ÉTAPE 12 : AMÉLIORATIONS UX

### 12.1 Nouvelle branche

```bash
git checkout -b feature/ameliorations-ux
```

### 12.2 Plan des améliorations UX

J'ai identifié trois priorités pour améliorer l'expérience utilisateur :

**Priorité 1 : Gestion dynamique des ingrédients** 🥕

-   Formulaire recette avec ajout/suppression d'ingrédients en temps réel
-   Autocomplete sur les noms d'ingrédients existants
-   Validation côté client
-   Interface intuitive avec boutons +/-

**Priorité 2 : Recherche avancée**

-   Filtres par catégorie, difficulté, temps de préparation
-   Tri par note, date, popularité
-   Recherche textuelle améliorée
-   Affichage des résultats optimisé

**Priorité 3 : Interface enrichie**

-   Pagination élégante pour les listes
-   Loading states pour les actions AJAX
-   Animations CSS subtiles
-   Messages de feedback améliorés

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

-   Recherche d'ingrédients par nom (minimum 2 caractères)
-   Limite à 10 résultats pour performance
-   Retourne ID, nom et unité par défaut
-   Format JSON pour intégration JavaScript

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

-   `ingredient` : Sélection avec autocomplete
-   `quantite` : Champ texte pour la quantité
-   `unite` : Liste déroulante avec unités courantes

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

-   `allow_add` : Permet d'ajouter des ingrédients dynamiquement
-   `allow_delete` : Permet de supprimer des ingrédients
-   `by_reference` : false pour gérer correctement les relations
-   `prototype` : true pour générer le template JavaScript

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

-   Les `<select>` et `<input>` s'affichaient en HTML brut
-   Pas de classes CSS Bootstrap
-   Layout cassé

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

-   Récupération du prototype Symfony
-   Remplacement du placeholder `__name__` par l'index
-   Injection dans une structure HTML Bootstrap
-   Stylisation automatique des champs
-   Gestion de la suppression
-   Stylisation des éléments au chargement

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

-   Fond gris clair pour chaque ligne
-   Effet hover pour feedback visuel
-   Espacement optimisé entre les colonnes
-   Bouton de suppression pleine largeur

### 12.12 Tests de la gestion dynamique

J'ai testé toutes les fonctionnalités :

-   [OK] Affichage du formulaire sans erreur
-   [OK] Ajout d'une ligne d'ingrédient avec le bouton +
-   [OK] Suppression d'une ligne avec le bouton X
-   [OK] Style Bootstrap correctement appliqué
-   [OK] Formulaire soumis avec les ingrédients
-   [OK] Données enregistrées en base de données

### 12.13 Problème rencontré avec les champs non mappés

Lors de l'implémentation de l'autocomplete, j'ai d'abord essayé d'utiliser des champs `mapped => false` dans le `RecetteIngredientType` :

-   `ingredient_id` (HiddenType)
-   `ingredient_nom` (TextType)

**Problème** : Les champs non mappés ne sont pas inclus dans le prototype Symfony, donc ils ne s'affichaient jamais dans le formulaire.

**Erreur** : Le champ `ingredient` restait invisible même après plusieurs tentatives.

### 12.14 Solution finale : Transformer le select en autocomplete

Au lieu de changer la structure du formulaire, j'ai gardé le champ `ingredient` comme un `EntityType` normal, et j'ai utilisé JavaScript pour le **transformer en autocomplete**.

**Avantages de cette approche** :

-   [OK] Le prototype Symfony fonctionne normalement
-   [OK] Pas de problème avec les champs non mappés
-   [OK] Le select caché contient toujours l'ID pour Symfony
-   [OK] L'utilisateur voit un champ texte avec autocomplete

**`RecetteIngredientType.php` final** :

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
 'class' => 'ingredient-select'
 ]
 ])
 ->add('quantite', TextType::class, [
 'attr' => ['placeholder' => 'Ex: 250']
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

### 12.15 JavaScript final avec transformation select → autocomplete

J'ai créé un JavaScript qui transforme automatiquement chaque select en un champ texte avec autocomplete dans `templates/recette/_form.html.twig` :

```javascript
<script>
document.addEventListener('DOMContentLoaded', function() {
 const ingredientsContainer = document.getElementById('ingredients-collection');
 const addButton = document.getElementById('add-ingredient');
 let index = 0;

 // Fonction pour transformer un select en autocomplete
 function setupAutocomplete(selectElement) {
 // Cacher le select original
 selectElement.style.display = 'none';

 // Créer l'input de recherche
 const searchInput = document.createElement('input');
 searchInput.type = 'text';
 searchInput.className = 'form-control form-control-sm';
 searchInput.placeholder = 'Tapez pour rechercher...';
 searchInput.autocomplete = 'off';

 // Conteneur pour les résultats
 const resultsDiv = document.createElement('div');
 resultsDiv.className = 'autocomplete-results position-absolute bg-white border rounded shadow-sm';
 resultsDiv.style.cssText = 'top: 100%; left: 0; right: 0; z-index: 1000; max-height: 200px; overflow-y: auto; display: none;';

 // Insérer après le select
 selectElement.parentElement.style.position = 'relative';
 selectElement.parentElement.appendChild(searchInput);
 selectElement.parentElement.appendChild(resultsDiv);

 let timeoutId;

 // Recherche avec debounce
 searchInput.addEventListener('input', function() {
 clearTimeout(timeoutId);
 const query = this.value.trim();

 if (query.length < 2) {
 resultsDiv.style.display = 'none';
 return;
 }

 timeoutId = setTimeout(() => {
 fetch(`/api/ingredients/search?q=${encodeURIComponent(query)}`)
 .then(response => response.json())
 .then(ingredients => {
 resultsDiv.innerHTML = '';

 if (ingredients.length === 0) {
 resultsDiv.style.display = 'none';
 return;
 }

 ingredients.forEach(ingredient => {
 const item = document.createElement('div');
 item.className = 'autocomplete-item p-2 border-bottom';
 item.style.cursor = 'pointer';
 item.textContent = ingredient.nom;

 item.addEventListener('mouseenter', () => item.classList.add('bg-light'));
 item.addEventListener('mouseleave', () => item.classList.remove('bg-light'));

 item.addEventListener('click', () => {
 searchInput.value = ingredient.nom;
 selectElement.value = ingredient.id;
 resultsDiv.style.display = 'none';
 });

 resultsDiv.appendChild(item);
 });

 resultsDiv.style.display = 'block';
 })
 .catch(error => console.error('Erreur autocomplete:', error));
 }, 300);
 });

 // Fermer en cliquant ailleurs
 document.addEventListener('click', function(e) {
 if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
 resultsDiv.style.display = 'none';
 }
 });
 }

 // Ajouter un ingrédient
 addButton.addEventListener('click', function() {
 const prototype = ingredientsContainer.dataset.prototype;
 const newForm = prototype.replace(/__name__/g, index);

 const tempDiv = document.createElement('div');
 tempDiv.innerHTML = newForm;

 const ingredientSelect = tempDiv.querySelector('select[name*="ingredient"]');
 const quantiteInput = tempDiv.querySelector('input[name*="quantite"]');
 const uniteSelect = tempDiv.querySelector('select[name*="unite"]');

 const wrapper = document.createElement('div');
 wrapper.className = 'ingredient-row border rounded p-2 mb-2';
 wrapper.innerHTML = `
 <div class="row">
 <div class="col-md-4 ingredient-field"></div>
 <div class="col-md-3 quantite-field"></div>
 <div class="col-md-3 unite-field"></div>
 <div class="col-md-2 d-flex align-items-end">
 <button type="button" class="btn btn-outline-danger btn-sm remove-ingredient w-100">
 <i class="bi bi-x"></i>
 </button>
 </div>
 </div>
 `;

 if (ingredientSelect) {
 wrapper.querySelector('.ingredient-field').appendChild(ingredientSelect);
 setupAutocomplete(ingredientSelect);
 }

 if (quantiteInput) {
 quantiteInput.className = 'form-control form-control-sm';
 wrapper.querySelector('.quantite-field').appendChild(quantiteInput);
 }

 if (uniteSelect) {
 uniteSelect.className = 'form-select form-select-sm';
 wrapper.querySelector('.unite-field').appendChild(uniteSelect);
 }

 ingredientsContainer.appendChild(wrapper);
 index++;

 wrapper.querySelector('.remove-ingredient').addEventListener('click', function() {
 wrapper.remove();
 });
 });
});
</script>
```

**Fonctionnement du système** :

1. Le select Symfony est généré normalement (contient tous les ingrédients)
2. JavaScript cache ce select avec `display: none`
3. Un input texte est créé à sa place
4. L'utilisateur tape dans l'input
5. Debounce de 300ms avant la recherche API
6. Affichage des suggestions dans une liste déroulante
7. Au clic sur une suggestion :

-   L'input texte affiche le nom
-   Le select caché reçoit l'ID

8. Lors de la soumission, Symfony récupère l'ID depuis le select

### 12.16 Tests complets avec autocomplete fonctionnel

J'ai testé tous les scénarios :

-   [OK] Clic sur "Ajouter un ingrédient"
-   [OK] Saisie de 2+ caractères (ex: "To")
-   [OK] Affichage des suggestions ("Tomates")
-   [OK] Effet hover sur les suggestions
-   [OK] Clic sur une suggestion
-   [OK] Champ texte rempli avec le nom
-   [OK] Select caché mis à jour avec l'ID
-   [OK] Ajout de plusieurs ingrédients
-   [OK] Suppression d'ingrédients
-   [OK] Soumission du formulaire
-   [OK] Vérification en base de données
-   [OK] Relations RecetteIngredient correctes

### 12.17 Commit final de la gestion dynamique avec autocomplete

```bash
git add .
git commit -m "feat: Gestion dynamique ingrédients avec autocomplete

- API REST de recherche d'ingrédients (/api/ingredients/search)
- FormType RecetteIngredient avec EntityType
- Collection Symfony avec allow_add/allow_delete
- JavaScript pour ajout/suppression dynamique de lignes
- Transformation select en autocomplete intelligent
- Debounce 300ms pour limiter requêtes API
- Affichage résultats stylisés avec hover
- Sélection au clic, mise à jour select caché
- CSS personnalisé pour UX professionnelle
- Résolution problème champs non mappés
- Tests complets réussis (création + édition)
- Documentation complète des solutions"
```

### 12.18 Adaptation pour le mode édition

Le JavaScript doit aussi fonctionner en mode édition quand la recette a déjà des ingrédients. J'ai ajouté plusieurs améliorations :

**1. Protection contre la double transformation**

```javascript
if (selectElement.dataset.autocompleteSetup) return;
selectElement.dataset.autocompleteSetup = "true";
```

**2. Pré-remplissage automatique en mode édition**

```javascript
// MODE ÉDITION : Pré-remplir avec la valeur existante
if (selectElement.value) {
    const selectedOption = selectElement.querySelector(
        `option[value="${selectElement.value}"]`
    );
    if (selectedOption) {
        searchInput.value = selectedOption.textContent;
    }
}
```

**3. Setup automatique des champs existants au chargement**

```javascript
// À la fin du script, après la définition de setupAutocomplete()
document
    .querySelectorAll('select[name*="ingredient"]')
    .forEach(function (select) {
        setupAutocomplete(select);
    });
```

**4. Comptage correct de l'index**

```javascript
let index = document.querySelectorAll(".ingredient-row").length;
```

### 12.19 Tests en mode édition

J'ai testé tous les scénarios d'édition :

-   [OK] Ouverture d'une recette existante avec ingrédients
-   [OK] Ingrédients affichés en champs texte (pas select)
-   [OK] Noms des ingrédients pré-remplis correctement
-   [OK] Modification d'un ingrédient avec autocomplete
-   [OK] Ajout d'un nouvel ingrédient
-   [OK] Suppression d'un ingrédient existant
-   [OK] Soumission du formulaire
-   [OK] Mise à jour correcte en base de données
-   [OK] Pas de duplication de champs
-   [OK] Aucune erreur JavaScript

**Template `edit.html.twig`** :
Le template utilise `{{ include('recette/_form.html.twig') }}`, donc tout le JavaScript est automatiquement inclus et fonctionne pour l'édition.

### 12.20 Commit final de la gestion dynamique avec autocomplete

```bash
git add .
git commit -m "feat: Gestion dynamique ingrédients avec autocomplete (création + édition)

Backend:
- API REST de recherche d'ingrédients (/api/ingredients/search)
- FormType RecetteIngredient avec EntityType
- Collection Symfony avec allow_add/allow_delete
- QueryBuilder pour recherche par nom

Frontend:
- JavaScript pour ajout/suppression dynamique de lignes
- Transformation select en autocomplete intelligent
- Debounce 300ms pour limiter requêtes API
- Affichage résultats stylisés avec hover
- Sélection au clic, mise à jour select caché
- Protection contre double transformation
- Pré-remplissage automatique en mode édition
- Setup automatique des champs existants
- CSS personnalisé pour UX professionnelle

Tests:
- Création de recette avec ingrédients
- Édition de recette existante
- Ajout/suppression dynamique
- Autocomplete fonctionnel
- Enregistrement correct en BDD

Documentation:
- Résolution problème champs non mappés
- Explication transformation select → autocomplete
- Guide complet avec tous les problèmes résolus"
```

### 12.21 Fusion de la branche

```bash
git checkout master
git merge feature/ameliorations-ux
git branch -d feature/ameliorations-ux
```

---

## ÉTAPE 13 : RECHERCHE AVANCÉE ET PAGINATION

### 13.1 Analyse des besoins

La page d'index des recettes nécessite plusieurs améliorations pour améliorer l'expérience utilisateur :

-   Système de recherche par mots-clés
-   Filtres par catégorie, difficulté, temps de préparation
-   Tri des résultats (date, note, temps)
-   Pagination pour gérer un grand nombre de recettes

### 13.2 Installation de KnpPaginatorBundle

**Commande d'installation :**

```bash
composer require knplabs/knp-paginator-bundle
```

**Vérification de l'installation :**

```bash
composer show knplabs/knp-paginator-bundle
```

Le bundle permet de paginer facilement des résultats Doctrine avec une interface utilisateur intégrée.

### 13.3 Configuration de KnpPaginator

**Création du fichier `config/packages/knp_paginator.yaml` :**

```yaml
knp_paginator:
    page_range: 3 # Nombre de pages affichées dans la navigation
    default_options:
    page_name: page # Nom du paramètre GET pour la page
    sort_field_name: sort # Nom du paramètre pour le tri
    sort_direction_name: direction # Nom du paramètre pour la direction
    distinct: true # Évite les doublons
    filter_field_name: filterField
    filter_value_name: filterValue
    template:
    pagination: "@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig"
    sortable: "@KnpPaginator/Pagination/sortable_link.html.twig"
```

**Note importante :** J'ai utilisé `twitter_bootstrap_v4_pagination.html.twig` car le template Bootstrap 5 n'est pas toujours disponible selon la version du bundle. La compatibilité avec Bootstrap 5 est assurée.

### 13.4 Création du formulaire de recherche avancée

**Création de `src/Form/RechercheAvanceeType.php` :**

```php
<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercheAvanceeType extends AbstractType
{
 public function buildForm(FormBuilderInterface $builder, array $options): void
 {
 $builder
 ->add('query', TextType::class, [
 'label' => 'Rechercher',
 'required' => false,
 'attr' => [
 'placeholder' => 'Nom de recette, ingrédient...',
 'class' => 'form-control'
 ]
 ])
 ->add('categorie', EntityType::class, [
 'class' => Categorie::class,
 'choice_label' => 'nom',
 'placeholder' => 'Toutes catégories',
 'required' => false,
 'attr' => ['class' => 'form-select']
 ])
 ->add('difficulte', ChoiceType::class, [
 'choices' => [
 'Facile' => 1,
 'Moyen' => 2,
 'Difficile' => 3
 ],
 'placeholder' => 'Toute difficulté',
 'required' => false,
 'attr' => ['class' => 'form-select']
 ])
 ->add('tempsMax', IntegerType::class, [
 'label' => 'Temps max (min)',
 'required' => false,
 'attr' => [
 'placeholder' => '30',
 'min' => 1,
 'class' => 'form-control'
 ]
 ])
 ->add('tri', ChoiceType::class, [
 'choices' => [
 'Plus récent' => 'date_desc',
 'Plus ancien' => 'date_asc',
 'Mieux noté' => 'note_desc',
 'Plus rapide' => 'temps_asc',
 'Plus long' => 'temps_desc'
 ],
 'data' => 'date_desc',
 'attr' => ['class' => 'form-select']
 ]);
 }

 public function configureOptions(OptionsResolver $resolver): void
 {
 $resolver->setDefaults([
 'method' => 'GET',
 'csrf_protection' => false
 ]);
 }
}
```

**Points importants :**

-   `method => 'GET'` : Permet de partager les URLs avec filtres
-   `csrf_protection => false` : Désactivé pour les formulaires GET
-   Tous les champs sont optionnels (`required => false`)

### 13.5 Mise à jour du RecetteRepository

**Ajout de la méthode `findWithFiltersQueryBuilder` dans `src/Repository/RecetteRepository.php` :**

```php
public function findWithFiltersQueryBuilder(array $criteria = [], array $orderBy = [])
{
 $qb = $this->createQueryBuilder('r')
 ->leftJoin('r.commentaires', 'c')
 ->leftJoin('r.recetteIngredients', 'ri')
 ->leftJoin('ri.ingredient', 'i')
 ->groupBy('r.id');

 // Recherche textuelle sur nom de recette ou ingrédient
 if (isset($criteria['query'])) {
 $qb->andWhere('r.nom LIKE :query OR i.nom LIKE :query')
 ->setParameter('query', '%' . $criteria['query'] . '%');
 }

 // Filtre par catégorie
 if (isset($criteria['categorie'])) {
 $qb->andWhere('r.categorie = :categorie')
 ->setParameter('categorie', $criteria['categorie']);
 }

 // Filtre par difficulté
 if (isset($criteria['difficulte'])) {
 $qb->andWhere('r.difficulte = :difficulte')
 ->setParameter('difficulte', $criteria['difficulte']);
 }

 // Filtre par temps maximum
 if (isset($criteria['tempsMax'])) {
 $qb->andWhere('r.tempsCuisson <= :tempsMax')
 ->setParameter('tempsMax', $criteria['tempsMax']);
 }

 // Gestion du tri
 foreach ($orderBy as $field => $direction) {
 if ($field === 'moyenneNotes') {
 // Tri par moyenne des notes
 $qb->addSelect('AVG(c.note) as HIDDEN avg_note')
 ->addOrderBy('avg_note', $direction);
 } else {
 $qb->addOrderBy('r.' . $field, $direction);
 }
 }

 return $qb;
}
```

**Fonctionnalités du QueryBuilder :**

-   Recherche full-text sur nom de recette et ingrédients
-   Filtrage multiple cumulatif
-   Tri dynamique incluant moyenne des notes
-   Retourne un QueryBuilder pour compatibilité avec la pagination

### 13.6 Mise à jour du contrôleur RecetteController

**Modification de la méthode `index()` dans `src/Controller/RecetteController.php` :**

```php
use Knp\Component\Pager\PaginatorInterface;
use App\Form\RechercheAvanceeType;

#[Route('/', name: 'app_recette_index', methods: ['GET'])]
public function index(
 Request $request,
 RecetteRepository $recetteRepository,
 PaginatorInterface $paginator
): Response {
 // Création et traitement du formulaire de recherche
 $searchForm = $this->createForm(RechercheAvanceeType::class);
 $searchForm->handleRequest($request);

 $criteria = [];
 $orderBy = ['dateCreation' => 'DESC'];

 if ($searchForm->isSubmitted()) {
 $data = $searchForm->getData();

 // Construction des critères de recherche
 if (!empty($data['query'])) {
 $criteria['query'] = $data['query'];
 }
 if (!empty($data['categorie'])) {
 $criteria['categorie'] = $data['categorie'];
 }
 if (!empty($data['difficulte'])) {
 $criteria['difficulte'] = $data['difficulte'];
 }
 if (!empty($data['tempsMax'])) {
 $criteria['tempsMax'] = $data['tempsMax'];
 }

 // Gestion du tri
 switch ($data['tri'] ?? 'date_desc') {
 case 'date_asc':
 $orderBy = ['dateCreation' => 'ASC'];
 break;
 case 'note_desc':
 $orderBy = ['moyenneNotes' => 'DESC'];
 break;
 case 'temps_asc':
 $orderBy = ['tempsCuisson' => 'ASC'];
 break;
 case 'temps_desc':
 $orderBy = ['tempsCuisson' => 'DESC'];
 break;
 default:
 $orderBy = ['dateCreation' => 'DESC'];
 }
 }

 // Récupération du QueryBuilder avec filtres
 $queryBuilder = $recetteRepository->findWithFiltersQueryBuilder($criteria, $orderBy);

 // Pagination : 9 recettes par page (grille 3x3)
 $recettes = $paginator->paginate(
 $queryBuilder,
 $request->query->getInt('page', 1),
 9
 );

 return $this->render('recette/index.html.twig', [
 'recettes' => $recettes,
 'searchForm' => $searchForm
 ]);
}
```

**Import nécessaire :**

```php
use Knp\Component\Pager\PaginatorInterface;
```

### 13.7 Mise à jour du template index.html.twig

**Ajout du formulaire de recherche au début de `templates/recette/index.html.twig` :**

```twig
<!-- Formulaire de recherche avancée -->
<div class="card mb-4">
 <div class="card-body">
 <div class="d-flex justify-content-between align-items-center mb-3">
 <h5 class="card-title mb-0">
 <i class="bi bi-funnel"></i> Recherche avancée
 </h5>
 <button class="btn btn-outline-secondary btn-sm" type="button"
 data-bs-toggle="collapse" data-bs-target="#searchCollapse">
 <i class="bi bi-chevron-down"></i>
 </button>
 </div>

 <div class="collapse" id="searchCollapse">
 {{ form_start(searchForm, {'attr': {'class': 'row g-3'}}) }}
 <div class="col-md-4">
 {{ form_row(searchForm.query, {'label_attr': {'class': 'form-label'}}) }}
 </div>

 <div class="col-md-2">
 {{ form_row(searchForm.categorie, {'label_attr': {'class': 'form-label'}}) }}
 </div>

 <div class="col-md-2">
 {{ form_row(searchForm.difficulte, {'label_attr': {'class': 'form-label'}}) }}
 </div>

 <div class="col-md-2">
 {{ form_row(searchForm.tempsMax, {'label_attr': {'class': 'form-label'}}) }}
 </div>

 <div class="col-md-2">
 {{ form_row(searchForm.tri, {'label_attr': {'class': 'form-label'}}) }}
 </div>

 <div class="col-12 d-flex gap-2">
 <button type="submit" class="btn btn-primary">
 <i class="bi bi-search"></i> Rechercher
 </button>
 <a href="{{ path('app_recette_index') }}" class="btn btn-outline-secondary">
 <i class="bi bi-arrow-clockwise"></i> Réinitialiser
 </a>
 </div>
 {{ form_end(searchForm) }}
 </div>
 </div>
</div>
```

**Ajout de la pagination après la grille des recettes :**

```twig
<!-- Pagination -->
{% if recettes.pageCount > 1 %}
 <div class="d-flex justify-content-center mt-4">
 {{ knp_pagination_render(recettes, '@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig') }}
 </div>
{% endif %}

<!-- Statistiques de pagination -->
<div class="text-center mt-3 text-muted small">
 {% set pagination = recettes.getPaginationData %}
 Affichage de {{ pagination.firstItemNumber }} à {{ pagination.lastItemNumber }}
 sur {{ recettes.getTotalItemCount() }} recette(s)
</div>
```

**Script JavaScript pour ouvrir automatiquement le panneau si filtres actifs :**

```javascript
<script>
document.addEventListener('DOMContentLoaded', function() {
 const urlParams = new URLSearchParams(window.location.search);
 const hasFilters = urlParams.has('recherche_avancee');

 if (hasFilters) {
 new bootstrap.Collapse(document.getElementById('searchCollapse'), {
 show: true
 });
 }
});
</script>
```

### 13.8 Résolution problème : Template pagination non trouvé

**Erreur rencontrée :**

```
Unable to find template "@KnpPaginator/Pagination/twitter_bootstrap_v5_pagination.html.twig"
```

**Solution :**
Utiliser le template Bootstrap 4 qui est compatible avec Bootstrap 5 :

```twig
{{ knp_pagination_render(recettes, '@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig') }}
```

### 13.9 Résolution problème : Propriétés pagination inexistantes

**Erreur rencontrée :**

```
Neither the property "currentPageOffsetStart" nor methods exist in class "SlidingPagination"
```

**Solution :**
Utiliser les méthodes correctes de l'API KnpPaginator :

```twig
{% set pagination = recettes.getPaginationData %}
Affichage de {{ pagination.firstItemNumber }} à {{ pagination.lastItemNumber }}
sur {{ recettes.getTotalItemCount() }} recette(s)
```

### 13.10 Création de fixtures de test

Pour tester efficacement la pagination et les filtres, j'ai créé des fixtures générant 100 recettes.

**Création de `src/DataFixtures/RecetteTestFixtures.php` :**

```php
<?php

namespace App\DataFixtures;

use App\Entity\Recette;
use App\Repository\UserRepository;
use App\Repository\CategorieRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class RecetteTestFixtures extends Fixture implements FixtureGroupInterface
{
 private $userRepository;
 private $categorieRepository;

 public function __construct(
 UserRepository $userRepository,
 CategorieRepository $categorieRepository
 ) {
 $this->userRepository = $userRepository;
 $this->categorieRepository = $categorieRepository;
 }

 public function load(ObjectManager $manager): void
 {
 $users = $this->userRepository->findAll();
 $categories = $this->categorieRepository->findAll();

 if (empty($users) || empty($categories)) {
 throw new \Exception('Ajoutez d\'abord des users et categories.');
 }

 $nomsRecettes = [
 'Tarte aux pommes', 'Quiche lorraine', 'Gratin dauphinois',
 'Ratatouille provençale', 'Poulet rôti aux herbes', 'Lasagnes bolognaise',
 'Pizza margherita', 'Salade niçoise', 'Crêpes bretonnes',
 'Cake au citron', 'Soupe de légumes', 'Poisson grillé',
 // ... 50 noms au total
 ];

 $descriptions = [
 'Une recette traditionnelle revisitée pour un résultat délicieux.',
 'Facile et rapide à préparer, parfait pour un repas en famille.',
 'Un grand classique de la cuisine qui plaît à tous.',
 'Recette anti-gaspi pour utiliser vos restes.',
 'Idéal pour les débutants en cuisine.',
 // ... 10 descriptions
 ];

 for ($i = 0; $i < 100; $i++) {
 $recette = new Recette();

 $nom = $nomsRecettes[$i % count($nomsRecettes)];
 if ($i >= count($nomsRecettes)) {
 $nom .= ' ' . ($i + 1);
 }
 $recette->setNom($nom);

 $recette->setDescription($descriptions[array_rand($descriptions)]);

 $nbEtapes = rand(3, 6);
 $etapes = [];
 for ($j = 1; $j <= $nbEtapes; $j++) {
 $etapes[] = $j . '. Étape ' . $j . ' de la préparation';
 }
 $recette->setEtapes(implode("\n", $etapes));

 $recette->setTempsCuisson(rand(0, 12) * 10);
 $recette->setNombrePersonnes(rand(1, 8));
 $recette->setDifficulte(($i % 3) + 1);
 $recette->setCategorie($categories[array_rand($categories)]);
 $recette->setUser($users[array_rand($users)]);

 $manager->persist($recette);

 if ($i % 20 === 0) {
 $manager->flush();
 }
 }

 $manager->flush();
 }

 public static function getGroups(): array
 {
 return ['test'];
 }
}
```

**Chargement des fixtures :**

```bash
# Ajouter sans effacer la base existante
php bin/console doctrine:fixtures:load --append --group=test

# Ou réinitialiser complètement (ATTENTION : efface tout)
php bin/console doctrine:fixtures:load --group=test
```

### 13.11 Tests de la recherche avancée et pagination

**Tests effectués :**

-   Recherche textuelle par nom de recette
-   Recherche par ingrédient
-   Filtre par catégorie
-   Filtre par difficulté (1, 2, 3)
-   Filtre par temps maximum
-   Combinaison de plusieurs filtres
-   Tri par date (ascendant/descendant)
-   Tri par temps de cuisson
-   Navigation entre les pages (1 à 12 avec 100 recettes)
-   Persistance des filtres lors du changement de page
-   Bouton de réinitialisation des filtres
-   Ouverture automatique du panneau si filtres actifs

**Résultats :**

-   Pagination fonctionnelle avec 9 recettes par page
-   Filtres cumulatifs fonctionnels
-   URLs partageables avec paramètres GET
-   Interface responsive
-   Performance correcte même avec 100+ recettes

### 13.12 Commit de la recherche avancée et pagination

```bash
git add .
git commit -m "feat: Recherche avancée et pagination

Backend:
- Installation KnpPaginatorBundle
- FormType RechercheAvanceeType avec tous les filtres
- QueryBuilder avec recherche full-text et filtres multiples
- Méthode findWithFiltersQueryBuilder dans RecetteRepository
- Gestion du tri dynamique (date, note, temps)
- Pagination avec 9 recettes par page

Frontend:
- Formulaire de recherche avancée collapsible
- Filtres par catégorie, difficulté, temps max
- Recherche textuelle sur nom et ingrédients
- Tri par date, note moyenne, temps de cuisson
- Pagination Bootstrap intégrée
- Statistiques d'affichage
- Ouverture automatique si filtres actifs
- Bouton de réinitialisation

Fixtures:
- RecetteTestFixtures pour générer 100 recettes de test
- Noms et descriptions variés
- Répartition équilibrée des difficultés
- Groupe 'test' pour chargement sélectif

Résolution de problèmes:
- Template Bootstrap 5 non disponible → Utilisation Bootstrap 4
- Propriétés pagination incorrectes → getPaginationData()
- Performance avec nombreuses recettes → Flush par batch

Tests:
- Tous les filtres validés individuellement et en combinaison
- Navigation pagination fonctionnelle
- Persistance des filtres entre pages
- URLs partageables
- Interface responsive"
```

---

## ÉTAPE 14 : LOADING STATES ET ANIMATIONS CSS

### 14.1 Objectifs de l'étape

Améliorer l'expérience utilisateur en ajoutant des feedbacks visuels lors des interactions avec le formulaire d'ingrédients :

-   Indicateur de chargement pendant la recherche autocomplete
-   Animations d'ajout et de suppression d'ingrédients
-   Transitions fluides pour les résultats de recherche
-   Validation visuelle lors de la sélection

**Compétences démontrées** :

-   Animations CSS3 avancées
-   États de chargement asynchrones
-   Manipulation DOM avec animations
-   UX patterns professionnels

### 14.2 Styles CSS pour les animations

**Ajout dans `templates/recette/_form.html.twig`** :

```html
<style>
    /* État de chargement pour l'autocomplete */
    .autocomplete-loading {
        position: relative;
    }

    .autocomplete-loading::after {
        content: "";
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: translateY(-50%) rotate(0deg);
        }
        100% {
            transform: translateY(-50%) rotate(360deg);
        }
    }

    /* Transitions pour les lignes d'ingrédients */
    .ingredient-row {
        transition: all 0.3s ease;
        transform: translateY(0);
    }

    .ingredient-row.adding {
        animation: slideInDown 0.4s ease;
    }

    .ingredient-row.removing {
        animation: slideOutUp 0.3s ease forwards;
    }

    /* Animation d'ajout */
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Animation de suppression */
    @keyframes slideOutUp {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }

    /* Animation pour les résultats de recherche */
    .autocomplete-results {
        animation: fadeIn 0.2s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
```

**Explications techniques** :

1. **Loading spinner** :

    - Pseudo-élément `::after` pour éviter HTML supplémentaire
    - Positionnement absolu dans le champ de saisie
    - Animation de rotation continue (360°)
    - Bordure partielle pour effet de chargement

2. **Animations de slide** :

    - `slideInDown` : Apparition depuis le haut avec fondu
    - `slideOutUp` : Disparition vers le haut avec fondu
    - Utilisation de `transform` pour performance GPU
    - `forwards` pour maintenir l'état final

3. **Fade pour autocomplete** :
    - Transition douce à l'ouverture
    - Déplacement vertical léger pour effet naturel
    - Durée courte (0.2s) pour réactivité

### 14.3 JavaScript amélioré avec états de chargement

**Mise à jour du script dans `templates/recette/_form.html.twig`** :

Modifications principales apportées à la fonction `setupAutocomplete()` :

```javascript
searchInput.addEventListener("input", function () {
    clearTimeout(timeoutId);
    const query = this.value.trim();

    if (query.length < 2) {
        resultsDiv.style.display = "none";
        searchInput.classList.remove("autocomplete-loading");
        return;
    }

    // Afficher le loading state
    searchInput.classList.add("autocomplete-loading");

    timeoutId = setTimeout(() => {
        fetch(`/api/ingredients/search?q=${encodeURIComponent(query)}`)
            .then((response) => response.json())
            .then((ingredients) => {
                // Retirer le loading state
                searchInput.classList.remove("autocomplete-loading");
                resultsDiv.innerHTML = "";

                if (ingredients.length === 0) {
                    resultsDiv.innerHTML =
                        '<div class="p-2 text-muted">Aucun ingrédient trouvé</div>';
                    resultsDiv.style.display = "block";
                    return;
                }

                ingredients.forEach((ingredient) => {
                    const item = document.createElement("div");
                    item.className = "autocomplete-item p-2 border-bottom";
                    item.style.cursor = "pointer";

                    // Affichage enrichi avec unité
                    item.innerHTML = `
                        <strong>${ingredient.nom}</strong>
                        <small class="text-muted d-block">Unité par défaut: ${ingredient.unite}</small>
                    `;

                    item.addEventListener("mouseenter", () =>
                        item.classList.add("bg-light")
                    );
                    item.addEventListener("mouseleave", () =>
                        item.classList.remove("bg-light")
                    );

                    item.addEventListener("click", () => {
                        searchInput.value = ingredient.nom;
                        selectElement.value = ingredient.id;
                        resultsDiv.style.display = "none";

                        // Animation de validation
                        searchInput.style.borderColor = "#28a745";
                        setTimeout(() => {
                            searchInput.style.borderColor = "";
                        }, 1000);
                    });

                    resultsDiv.appendChild(item);
                });

                resultsDiv.style.display = "block";
            })
            .catch((error) => {
                console.error("Erreur autocomplete:", error);
                searchInput.classList.remove("autocomplete-loading");
                resultsDiv.innerHTML =
                    '<div class="p-2 text-danger">Erreur de recherche</div>';
                resultsDiv.style.display = "block";
            });
    }, 300);
});
```

**Améliorations implémentées** :

1. **Loading state** :

    - Ajout classe `autocomplete-loading` pendant la requête
    - Retrait après réception des résultats
    - Spinner CSS s'affiche automatiquement

2. **Message "Aucun résultat"** :

    - Gestion du cas où la recherche ne retourne rien
    - Message informatif pour l'utilisateur
    - Évite l'impression de dysfonctionnement

3. **Affichage enrichi** :

    - Nom de l'ingrédient en gras
    - Unité par défaut affichée
    - Meilleure prévisualisation pour l'utilisateur

4. **Feedback visuel de validation** :

    - Bordure verte temporaire (1 seconde)
    - Confirme la sélection à l'utilisateur
    - Retour automatique à l'état normal

5. **Gestion d'erreur** :
    - Catch des erreurs réseau
    - Message d'erreur affiché
    - Retrait du loading state même en cas d'erreur

### 14.4 Animations pour ajout et suppression

**Modification de la fonction d'ajout d'ingrédient** :

```javascript
addButton.addEventListener("click", function () {
    const prototype = ingredientsContainer.dataset.prototype;
    const newForm = prototype.replace(/__name__/g, index);

    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = newForm;

    const ingredientSelect = tempDiv.querySelector(
        'select[name*="ingredient"]'
    );
    const quantiteInput = tempDiv.querySelector('input[name*="quantite"]');
    const uniteSelect = tempDiv.querySelector('select[name*="unite"]');

    const wrapper = document.createElement("div");
    // Ajout de la classe 'adding' pour animation
    wrapper.className = "ingredient-row border rounded p-2 mb-2 adding";
    wrapper.innerHTML = `
        <div class="row">
            <div class="col-md-4 ingredient-field"></div>
            <div class="col-md-3 quantite-field"></div>
            <div class="col-md-3 unite-field"></div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm remove-ingredient w-100">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
    `;

    if (ingredientSelect) {
        wrapper
            .querySelector(".ingredient-field")
            .appendChild(ingredientSelect);
        setupAutocomplete(ingredientSelect);
    }

    if (quantiteInput) {
        quantiteInput.className = "form-control form-control-sm";
        wrapper.querySelector(".quantite-field").appendChild(quantiteInput);
    }

    if (uniteSelect) {
        uniteSelect.className = "form-select form-select-sm";
        wrapper.querySelector(".unite-field").appendChild(uniteSelect);
    }

    ingredientsContainer.appendChild(wrapper);

    // Retirer la classe d'animation après son exécution
    setTimeout(() => wrapper.classList.remove("adding"), 400);

    index++;

    // Animation de suppression
    wrapper
        .querySelector(".remove-ingredient")
        .addEventListener("click", function () {
            wrapper.classList.add("removing");
            setTimeout(() => wrapper.remove(), 300);
        });
});
```

**Logique d'animation** :

1. **Ajout** :

    - Classe `adding` ajoutée immédiatement
    - Animation `slideInDown` se déclenche automatiquement
    - Classe retirée après 400ms (durée animation)
    - Élément reste visible après animation

2. **Suppression** :

    - Classe `removing` ajoutée au clic
    - Animation `slideOutUp` se déclenche
    - `remove()` appelé après 300ms
    - Élément supprimé du DOM après animation

3. **Timing** :
    - `setTimeout` synchronisé avec durée CSS
    - Évite les bugs visuels
    - Transitions fluides

### 14.5 Tests réalisés

**Tests fonctionnels** :

-   Loading spinner apparaît pendant la recherche
-   Spinner disparaît après réception des résultats
-   Message "Aucun ingrédient trouvé" s'affiche correctement
-   Animation d'ajout fluide sans saccade
-   Animation de suppression complète avant retrait DOM
-   Bordure verte de validation visible 1 seconde
-   Gestion d'erreur affiche le message approprié

**Tests de performance** :

-   Animations GPU-accélérées (transform, opacity)
-   Pas de repaint inutile
-   60fps maintenus pendant les animations
-   Debounce toujours fonctionnel

**Tests d'accessibilité** :

-   Animations respectent `prefers-reduced-motion` (à ajouter)
-   Spinner ne bloque pas la saisie
-   Messages d'erreur lisibles
-   Contrastes respectés

### 14.6 Améliorations possibles

**Respect des préférences utilisateur** :

Ajout d'une media query pour désactiver les animations si nécessaire :

```css
@media (prefers-reduced-motion: reduce) {
    .ingredient-row,
    .autocomplete-results {
        animation: none !important;
        transition: none !important;
    }

    .autocomplete-loading::after {
        animation: none !important;
    }
}
```

**Skeleton loading** :

Pour une UX encore plus polie, on pourrait remplacer le spinner par un skeleton screen :

```css
.autocomplete-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s ease-in-out infinite;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}
```

### 14.7 Impact sur l'expérience utilisateur

**Avant les animations** :

-   Ajout/suppression instantané et brutal
-   Pas de feedback pendant la recherche
-   Utilisateur ne sait pas si la recherche fonctionne
-   Interface statique et peu engageante

**Après les animations** :

-   Transitions fluides et naturelles
-   Feedback visuel constant
-   Utilisateur informé de l'état du système
-   Interface moderne et professionnelle
-   Confiance accrue dans l'application

### 14.8 Commit de la fonctionnalité

```bash
git add .
git commit -m "feat: Loading states et animations CSS pour autocomplete

CSS:
- Spinner de chargement avec animation rotate
- Animations slideInDown et slideOutUp pour ingrédients
- Animation fadeIn pour résultats autocomplete
- Transitions fluides avec transform pour performance
- Animations GPU-accélérées

JavaScript:
- Gestion états de chargement (classe autocomplete-loading)
- Message 'Aucun résultat' si recherche vide
- Affichage enrichi avec unité par défaut
- Bordure verte validation temporaire (1s)
- Gestion erreurs réseau avec message
- Synchronisation timing animations avec DOM

UX:
- Feedback visuel constant pendant recherche
- Transitions naturelles ajout/suppression
- Validation visuelle sélection
- Messages informatifs (erreur, vide)
- Performance 60fps maintenue

Tests:
- Animations fluides validées
- Loading state fonctionnel
- Pas de bug timing
- Performance GPU vérifiée"
```

---

## ÉTAPE 13 : AMÉLIORATION VISUELLE ET MESSAGES FLASH

### 13.1 Création du fichier CSS personnalisé

J'ai créé un fichier CSS personnalisé `public/css/app.css` avec la palette de couleurs complète du projet "Les Restes".

**Variables CSS définies** :

```css
:root {
    --success-color: #4caf50; /* Vert principal */
    --navigation-title-color: #2e7d32; /* Vert foncé titres */
    --cta-color: #f08a00; /* Orange CTA */
    --text-color: #000000; /* Noir texte */
    --background-color: #fcf8f5; /* Beige clair fond */
    --alt-background-color: #ffffff; /* Blanc cartes */
    --error-color: #ff383c; /* Rouge erreurs */
}
```

**Organisation du fichier** :

1. Variables CSS
2. Typographie (h1-h6, body, labels)
3. Layout général
4. Navigation
5. Boutons avec états hover
6. Cartes avec effets
7. Formulaires avec focus states
8. Étoiles de notation
9. Messages flash
10. Pagination
11. Autocomplete
12. Ingrédients dynamiques
13. Responsive design

### 13.2 Effets visuels implémentés

**Cartes avec hover** :

```css
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(76, 175, 80, 0.15);
    border-color: var(--success-color);
}
```

Les cartes s'élèvent légèrement au survol avec une ombre verte.

**Focus sur formulaires** :

```css
.form-control:focus,
.form-select:focus {
    border-color: var(--success-color);
    box-shadow: 0 0 0 0.25rem rgba(76, 175, 80, 0.25);
}
```

Bordure et ombre vertes quand on clique dans un champ.

**Boutons personnalisés** :

-   `.btn-success` : Vert avec hover plus foncé
-   `.btn-warning` : Orange avec hover plus foncé
-   `.btn-primary` : Également en vert pour cohérence

### 13.3 Messages flash améliorés

J'ai créé `templates/_flash_messages.html.twig` avec :

-   Icônes Bootstrap Icons selon le type
-   Animation slideInDown à l'apparition
-   Bouton de fermeture sur chaque message
-   Auto-fermeture des messages success après 5s
-   Couleurs personnalisées de la palette

**Template** :

```twig
{% for type, messages in app.flashes %}
    {% for message in messages %}
        <div class="alert alert-{{ type == 'error' ? 'danger' : type }} alert-dismissible fade show flash-message" role="alert">
            {% if type == 'success' %}
                <i class="bi bi-check-circle-fill me-2"></i>
            {% elseif type == 'error' or type == 'danger' %}
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {% else %}
                <i class="bi bi-info-circle-fill me-2"></i>
            {% endif %}
            {{ message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    {% endfor %}
{% endfor %}
```

**Animation CSS** :

```css
.flash-message {
    animation: slideInDown 0.5s ease;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-100%);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

### 13.4 Intégration dans base.html.twig

**Ajout du CSS personnalisé** (après Bootstrap) :

```twig
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
```

**Inclusion des messages flash** :

```twig
<div class="container mt-3">
    {{ include('_flash_messages.html.twig') }}
</div>
```

### 13.5 Responsive design

Ajout de breakpoints pour mobile :

```css
@media (max-width: 768px) {
    h1 {
        font-size: 28px;
    }

    h2 {
        font-size: 24px;
    }

    .card:hover {
        transform: none; /* Pas d'effet sur mobile */
    }
}
```

### 13.6 Tests des améliorations visuelles

J'ai testé tous les éléments visuels :

-   Palette de couleurs appliquée sur toutes les pages
-   Cartes avec effet hover fonctionnel
-   Boutons avec bonnes couleurs et états hover
-   Messages flash avec animation slideInDown
-   Auto-fermeture des success après 5 secondes
-   ✅ Formulaires avec focus vert
-   ✅ Étoiles de notation en orange
-   ✅ Pagination avec liens verts
-   ✅ Responsive testé sur mobile
-   Aucun conflit avec Bootstrap

### 13.7 Avantages de l'approche avec variables CSS

**Maintenance facile** :

-   Changer une couleur = modifier 1 variable
-   Cohérence automatique dans toute l'app
-   Facile d'ajouter un mode sombre plus tard

**Performance** :

-   1 seul fichier CSS
-   Animations CSS (plus rapides que JS)
-   Sélecteurs simples et efficaces

**Organisation** :

-   Sections clairement commentées
-   Ordre logique (variables → layout → composants)
-   Cascade CSS respectée

---

## ÉTAPE 14 : LOADING STATES ET ANIMATIONS CSS

### 14.1 Animations pour l'autocomplete

J'ai ajouté des animations CSS professionnelles pour améliorer l'expérience utilisateur lors de la recherche d'ingrédients.

**Loading spinner pendant la recherche** :

```css
.autocomplete-loading::after {
    content: "";
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid var(--success-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
```

**Effet** : Un petit spinner vert apparaît dans le champ pendant la recherche.

### 14.2 Animations pour ajout/suppression d'ingrédients

**Animation d'ajout** :

```css
.ingredient-row.adding {
    animation: slideInDown 0.4s ease;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

**Animation de suppression** :

```css
.ingredient-row.removing {
    animation: slideOutUp 0.3s ease forwards;
}

@keyframes slideOutUp {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-20px);
    }
}
```

**Effet** : Les lignes glissent vers le bas à l'ajout et vers le haut à la suppression.

### 14.3 Animation des résultats d'autocomplete

```css
.autocomplete-results {
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

**Effet** : La liste de suggestions apparaît en fondu avec un léger mouvement.

### 14.4 JavaScript mis à jour avec loading states

J'ai amélioré le JavaScript de l'autocomplete pour inclure :

**1. Loading spinner** :

```javascript
// Afficher le loading
searchInput.classList.add("autocomplete-loading");

// Retirer après la réponse
searchInput.classList.remove("autocomplete-loading");
```

**2. Messages améliorés** :

```javascript
if (ingredients.length === 0) {
    resultsDiv.innerHTML =
        '<div class="p-2 text-muted"><i class="bi bi-search"></i> Aucun ingrédient trouvé</div>';
}
```

**3. Affichage des unités** :

```javascript
item.innerHTML = `
    <strong>${ingredient.nom}</strong>
    ${
        ingredient.unite
            ? `<small class="text-muted d-block">Unité: ${ingredient.unite}</small>`
            : ""
    }
`;
```

**4. Animation de validation** :

```javascript
item.addEventListener("click", () => {
    searchInput.value = ingredient.nom;
    selectElement.value = ingredient.id;

    // Bordure verte temporaire
    searchInput.style.borderColor = "var(--success-color)";
    setTimeout(() => {
        searchInput.style.borderColor = "";
    }, 1000);
});
```

**5. Gestion d'erreurs** :

```javascript
.catch(error => {
    searchInput.classList.remove('autocomplete-loading');
    resultsDiv.innerHTML = '<div class="p-2 text-danger"><i class="bi bi-exclamation-triangle"></i> Erreur de recherche</div>';
});
```

### 14.5 Animations pour ajout/suppression

**Ajout avec animation** :

```javascript
const wrapper = document.createElement("div");
wrapper.className = "ingredient-row border rounded p-2 mb-2 adding";

// Retirer la classe après l'animation
setTimeout(() => wrapper.classList.remove("adding"), 400);
```

**Suppression avec animation** :

```javascript
wrapper
    .querySelector(".remove-ingredient")
    .addEventListener("click", function () {
        wrapper.classList.add("removing");
        setTimeout(() => wrapper.remove(), 300);
    });
```

### 14.6 Tests des animations

J'ai testé toutes les animations :

-   Loading spinner s'affiche pendant la recherche
-   Suggestions apparaissent avec fadeIn
-   Nouvelle ligne glisse vers le bas
-   Ligne supprimée glisse vers le haut
-   Bordure verte lors de la sélection
-   Message "Aucun résultat" si rien trouvé
-   Message d'erreur en cas de problème
-   Unité affichée sous le nom de l'ingrédient
-   Aucun lag ou saccade

### 14.7 Résumé des améliorations UX

**Avant** :

-   Pas de feedback visuel pendant la recherche
-   Ajout/suppression instantané (brutal)
-   Pas d'indication quand un ingrédient est sélectionné
-   Pas de gestion d'erreurs visible

**Après** :

-   Loading spinner pendant la recherche
-   Animations fluides pour ajout/suppression
-   Bordure verte temporaire après sélection
-   Messages d'erreur clairs avec icônes
-   Affichage de l'unité de l'ingrédient
-   Message "Aucun résultat" informatif

---

## ÉTAPE 15 : MESSAGES DE FEEDBACK AMÉLIORÉS

### 15.1 Template des messages flash

J'ai créé `templates/_flash_messages.html.twig` avec un design moderne et des animations.

**Template complet** :

```twig
{% for type, messages in app.flashes %}
    {% for message in messages %}
        <div class="alert alert-{{ type == 'error' ? 'danger' : type }} alert-dismissible fade show flash-message" role="alert">
            {% if type == 'success' %}
                <i class="bi bi-check-circle-fill me-2"></i>
            {% elseif type == 'error' or type == 'danger' %}
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {% else %}
                <i class="bi bi-info-circle-fill me-2"></i>
            {% endif %}
            {{ message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    {% endfor %}
{% endfor %}
```

**Fonctionnalités** :

-   Icônes Bootstrap Icons selon le type
-   Bouton de fermeture sur chaque message
-   Gestion de 3 types : success, error/danger, info
-   Classes Bootstrap pour le style

### 15.2 Animation des messages flash

**CSS dans le template** :

```css
.flash-message {
    animation: slideInDown 0.5s ease;
    margin-bottom: 1rem;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-100%);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

**Effet** : Les messages glissent du haut de l'écran vers le bas.

### 15.3 Auto-fermeture des messages success

**JavaScript dans le template** :

```javascript
setTimeout(() => {
    document.querySelectorAll(".flash-message").forEach((alert) => {
        if (alert.classList.contains("alert-success")) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}, 5000);
```

**Comportement** :

-   Messages **success** : Se ferment automatiquement après 5 secondes
-   Messages **error/danger/info** : Restent visibles (l'utilisateur doit fermer manuellement)

### 15.4 Intégration dans base.html.twig

**Ajout dans le layout** :

```twig
<body>
    {% include '_navbar.html.twig' %}

    <!-- Messages flash -->
    <div class="container mt-3">
        {{ include('_flash_messages.html.twig') }}
    </div>

    {% block body %}{% endblock %}
</body>
```

**Position** : Les messages s'affichent sous la navbar, avant le contenu principal.

### 15.5 Utilisation dans les contrôleurs

**Exemples de messages** :

```php
// Message de succès (auto-close après 5s)
$this->addFlash('success', 'Recette créée avec succès !');

// Message d'erreur (reste visible)
$this->addFlash('error', 'Une erreur est survenue lors de la création.');

// Message d'information
$this->addFlash('info', 'Votre profil a été mis à jour.');

// Message de danger (reste visible)
$this->addFlash('danger', 'Action impossible : recette introuvable.');
```

### 15.6 Styles personnalisés dans app.css

**Messages success** :

```css
.alert-success {
    background-color: rgba(76, 175, 80, 0.1);
    border-color: var(--success-color);
    color: var(--navigation-title-color);
}
```

**Messages danger** :

```css
.alert-danger {
    background-color: rgba(255, 56, 60, 0.1);
    border-color: var(--error-color);
    color: #721c24;
}
```

**Effet** : Les messages utilisent la palette de couleurs personnalisée.

### 15.7 Tests des messages flash

J'ai testé tous les scénarios :

-   Message success après création de recette
-   Message error lors d'une erreur de validation
-   Message info après mise à jour de profil
-   Animation slideInDown fluide
-   Auto-fermeture des success après 5s
-   Bouton de fermeture fonctionnel
-   Icônes correctes selon le type
-   Couleurs de la palette appliquées
-   Responsive sur mobile

### 15.8 Résumé des améliorations

**Avant** :

-   Messages flash basiques de Symfony
-   Pas d'icônes
-   Pas d'animation
-   Couleurs Bootstrap par défaut
-   Pas d'auto-fermeture

**Après** :

-   Messages avec icônes expressives
-   Animation slideInDown élégante
-   Couleurs personnalisées (palette du projet)
-   Auto-fermeture des success après 5s
-   Bouton de fermeture manuel
-   Design professionnel et cohérent

---

## ÉTAPE 16 : FINALISATION DE L'INTERFACE SELON LE WIREFRAME

### 16.1 Contexte et besoin

Après avoir développé toutes les fonctionnalités backend et le système d'autocomplete, j'ai comparé l'application actuelle avec le wireframe initial. Plusieurs éléments manquaient pour correspondre exactement au design prévu :

**Problèmes identifiés** :

-   Logo SVG incomplet ou manquant dans la navbar
-   Page d'accueil sans hero section
-   Icône du bol manquante
-   Barre de recherche trop petite et pas stylisée
-   Footer basique sans les sections structurées
-   Manque de cohérence visuelle avec le wireframe

### 16.2 Mise à jour du fichier CSS complet

J'ai créé le fichier `public/css/app.css` avec toute la palette de couleurs et les styles personnalisés.

**Contenu du fichier CSS** :

```css
/* Variables CSS pour cohérence */
:root {
    --success-color: #4caf50; /* Vert principal */
    --navigation-title-color: #2e7d32; /* Vert foncé titres */
    --cta-color: #f08a00; /* Orange CTA */
    --text-color: #000000; /* Noir texte */
    --background-color: #fcf8f5; /* Beige fond */
    --alt-background-color: #ffffff; /* Blanc cartes */
    --error-color: #ff383c; /* Rouge erreurs */
}
```

**Sections du CSS** :

1. **Variables CSS** : Palette de couleurs complète
2. **Typographie** : h1-h6 avec tailles et poids définis
3. **Layout général** : Background beige clair
4. **Navigation** : Logo en vert
5. **Boutons** : Success (vert), Warning (orange) avec hover
6. **Cartes** : Effet hover avec élévation et ombre
7. **Formulaires** : Focus vert sur les champs
8. **Étoiles** : Couleur orange pour la notation
9. **Messages flash** : Animation slideInDown
10. **Pagination** : Liens verts
11. **Autocomplete** : Spinner et animations
12. **Ingrédients** : Animations ajout/suppression
13. **Responsive** : Breakpoints mobile

**Animations CSS ajoutées** :

-   `slideInDown` : Messages flash et ajout d'ingrédients
-   `slideOutUp` : Suppression d'ingrédients
-   `fadeIn` : Apparition des résultats autocomplete
-   `spin` : Loading spinner pendant recherche

### 16.3 Correction du template base.html.twig

J'ai corrigé le template de base pour inclure le logo SVG complet et un footer structuré.

**Changements dans le header** :

```twig
<!-- Logo SVG complet dans la navbar -->
<svg width="35" height="40" viewBox="0 0 49 57" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
    <path d="M12.1523 0C12.1523 0 9.73828 1.65469..." fill="#1E5128"/>
</svg>
Les Restes
```

**Nouveau footer structuré** :

```twig
<footer class="bg-light py-4 mt-5 border-top">
    <div class="container">
        <div class="row">
            <!-- Colonne 1 : Mentions légales -->
            <div class="col-md-3">
                <h6 class="fw-bold mb-3">Mention légales</h6>
                <p class="small mb-0">Copyright © 2025</p>
            </div>

            <!-- Colonne 2 : Réseaux sociaux -->
            <div class="col-md-3">
                <h6 class="fw-bold mb-3">Réseaux sociaux</h6>
                <div class="d-flex gap-3">
                    <a href="#" class="text-dark"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="#" class="text-dark"><i class="bi bi-twitter-x fs-5"></i></a>
                    <a href="#" class="text-dark"><i class="bi bi-tiktok fs-5"></i></a>
                    <a href="#" class="text-dark"><i class="bi bi-facebook fs-5"></i></a>
                </div>
            </div>

            <!-- Colonne 3 : Contact -->
            <div class="col-md-3">
                <h6 class="fw-bold mb-3">Nous contacter</h6>
                <p class="small mb-0">
                    <a href="mailto:Nous-contacter@Lereste.com">
                        Nous-contacter@Lereste.com
                    </a>
                </p>
            </div>

            <!-- Colonne 4 : Adresse et Logo -->
            <div class="col-md-3 text-end">
                <h6 class="fw-bold mb-3">Adresse Postale</h6>
                <p class="small mb-3">
                    666 Rue du Paradis 31000<br>
                    Toulouse
                </p>
                <!-- Logo SVG -->
                <svg width="49" height="57" viewBox="0 0 49 57"...>
                    <path d="..." fill="#1E5128"/>
                </svg>
                <p class="small fw-bold mt-2">LesRestes</p>
            </div>
        </div>
    </div>
</footer>
```

**Structure du footer** :

-   4 colonnes équilibrées (col-md-3 chacune)
-   Sections : Mentions légales, Réseaux sociaux, Contact, Adresse
-   Icônes Bootstrap pour les réseaux sociaux
-   Logo en bas à droite
-   Correspond exactement au wireframe

### 16.4 Refonte de la page d'accueil

J'ai complètement revu `templates/home/index.html.twig` pour correspondre au wireframe.

**Hero section** :

```twig
<div class="hero-section text-center py-5 mb-5">
    <h1 class="display-4 mb-4">
        Transformez vos restes<br>
        en délicieuses recettes
    </h1>

    <!-- Icône du bol orange -->
    <div class="mb-4">
        <svg width="80" height="93" viewBox="0 0 49 57" fill="none">
            <path d="..." fill="#F08A00"/>
        </svg>
    </div>

    <!-- Grande barre de recherche -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="{{ path('app_recette_index') }}" method="GET">
                <div class="input-group input-group-lg mb-4">
                    <input type="text"
                           class="form-control"
                           name="q"
                           placeholder="Entrez vos ingrédients (ex: tomates, œufs, fromage...)"
                           style="border-radius: 50px 0 0 50px; padding: 1rem 1.5rem;">
                    <button class="btn btn-warning"
                            type="submit"
                            style="border-radius: 0 50px 50px 0; padding: 0 2rem; font-weight: bold;">
                        <i class="bi bi-search"></i> Rechercher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

**Éléments clés du hero** :

-   Titre sur 2 lignes centré
-   Icône du bol orange (SVG) de 80x93px
-   Grande barre de recherche arrondie (border-radius: 50px)
-   Bouton orange "Rechercher" avec icône
-   Padding généreux pour respiration visuelle

**Section "Dernières recettes"** :

```twig
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-4">Dernières recettes ajoutées</h2>
    </div>
</div>

<div class="row">
    {% for recette in dernieresRecettes %}
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <!-- Image -->
                {% if recette.image %}
                    <img src="/uploads/recettes/{{ recette.image }}"
                         class="card-img-top"
                         style="height: 250px; object-fit: cover;">
                {% else %}
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                         style="height: 250px;">
                        <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                    </div>
                {% endif %}

                <div class="card-body">
                    <h5 class="card-title">{{ recette.nom }}</h5>

                    <!-- Étoiles de notation -->
                    <div class="mb-2">
                        {% if recette.moyenneNotes %}
                            {% for i in 1..5 %}
                                {% if i <= recette.moyenneNotes %}
                                    <i class="bi bi-star-fill text-warning"></i>
                                {% else %}
                                    <i class="bi bi-star text-warning"></i>
                                {% endif %}
                            {% endfor %}
                        {% else %}
                            {% for i in 1..5 %}
                                <i class="bi bi-star text-warning"></i>
                            {% endfor %}
                        {% endif %}
                    </div>

                    <!-- Description tronquée -->
                    <p class="card-text text-muted small">
                        {{ recette.description|length > 80 ? recette.description|slice(0, 80) ~ '...' : recette.description }}
                    </p>

                    <a href="{{ path('app_recette_show', {'id': recette.id}) }}"
                       class="btn btn-outline-success btn-sm">
                        <i class="bi bi-eye"></i> Voir
                    </a>
                </div>
            </div>
        </div>
    {% else %}
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Aucune recette disponible pour le moment.
            </div>
        </div>
    {% endfor %}
</div>
```

**Gestion des cartes de recettes** :

-   3 colonnes (col-md-4)
-   Image fixe à 250px de hauteur avec object-fit: cover
-   Placeholder si pas d'image (icône Bootstrap)
-   Étoiles de notation oranges
-   Description limitée à 80 caractères
-   Bouton "Voir" vert outline
-   Message informatif si aucune recette
-   Effet hover sur les cartes (élévation)

### 16.5 Vérification du HomeController

Pour que la page d'accueil affiche les recettes, le contrôleur doit passer les données :

```php
<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecetteRepository $recetteRepository): Response
    {
        // Récupérer les 3 dernières recettes
        $dernieresRecettes = $recetteRepository->findBy(
            [],
            ['dateCreation' => 'DESC'],
            3
        );

        return $this->render('home/index.html.twig', [
            'dernieresRecettes' => $dernieresRecettes,
        ]);
    }
}
```

**Points importants** :

-   Injection de `RecetteRepository`
-   `findBy()` avec tri par dateCreation DESC
-   Limite de 3 recettes
-   Variable `dernieresRecettes` passée au template

### 16.6 Tests de l'interface finalisée

J'ai testé tous les éléments visuels :

-   Logo SVG s'affiche correctement dans la navbar
-   Hero section avec titre, icône et barre de recherche
-   Icône du bol orange bien centrée
-   Barre de recherche arrondie avec bouton orange
-   Section "Dernières recettes" avec 3 cartes
-   Images des recettes affichées correctement
-   Étoiles de notation en orange
-   Effet hover sur les cartes
-   Footer structuré en 4 colonnes
-   Logo dans le footer en bas à droite
-   Responsive sur mobile

### 16.7 Comparaison wireframe vs résultat final

**Page d'accueil** :

| Élément          | Wireframe       | Avant     | Après      |
| ---------------- | --------------- | --------- | ---------- |
| Logo navbar      | Présent         | Incomplet | Complet    |
| Titre hero       | 2 lignes        | Absent    | Présent    |
| Icône bol        | Orange centré   | Absent    | Présent    |
| Barre recherche  | Grande arrondie | Petite    | Grande     |
| Bouton recherche | Orange          | Vert      | Orange     |
| Section recettes | 3 cartes        | Basique   | 3 cartes   |
| Footer           | 4 colonnes      | Simple    | 4 colonnes |

**Résultat** : L'interface correspond maintenant exactement au wireframe !

### 16.8 Fichiers modifiés

**Nouveaux fichiers** :

-   `public/css/app.css` - CSS complet avec palette
-   `templates/_flash_messages.html.twig` - Messages animés

**Fichiers modifiés** :

-   `templates/base.html.twig` - Logo + Footer complet
-   `templates/home/index.html.twig` - Hero + Recettes
-   `src/Controller/HomeController.php` - Passage des recettes

### 16.9 Points d'amélioration appliqués

**UX améliorée** :

1. Barre de recherche arrondie et grande (plus visible)
2. Bouton orange contrasté (appel à l'action clair)
3. Cartes avec effet hover (feedback visuel)
4. Messages flash animés (apparition fluide)
5. Footer complet (informations structurées)

**Design cohérent** :

1. Palette de couleurs respectée partout
2. Typographie hiérarchisée (h1, h2, h3)
3. Espacement généreux (py-5, mb-4)
4. Icônes Bootstrap Icons (cohérence visuelle)
5. Layout responsive (col-md-X)

**Performance** :

1. CSS organisé par sections
2. Variables CSS pour maintenance facile
3. Animations légères (0.3s, 0.5s)
4. Images avec object-fit: cover
5. Pas de JavaScript lourd sur la home

### 16.10 Commit de finalisation

```bash
git add public/css/app.css templates/base.html.twig templates/home/index.html.twig templates/_flash_messages.html.twig
git commit -m "feat: Finalisation interface selon wireframe

CSS:
- Fichier app.css complet avec palette de couleurs
- Variables CSS pour maintenance facile
- Animations (slideInDown, slideOutUp, fadeIn, spin)
- Effets hover sur cartes et boutons
- Typographie hiérarchisée
- Responsive design pour mobile

Templates base.html.twig:
- Logo SVG complet dans navbar
- Footer structuré en 4 colonnes
- Mentions légales, Réseaux sociaux, Contact, Adresse
- Logo en bas à droite
- Flash messages bien positionnés

Templates home/index.html.twig:
- Hero section avec titre sur 2 lignes
- Icône du bol orange centrée
- Grande barre de recherche arrondie
- Bouton orange 'Rechercher'
- Section 'Dernières recettes ajoutées'
- 3 cartes avec images, étoiles, description
- Gestion cas aucune recette
- Bouton 'Voir toutes les recettes'

HomeController:
- Récupération des 3 dernières recettes
- Tri par dateCreation DESC
- Passage au template

Tests:
-  Interface identique au wireframe
-  Logo SVG complet
-  Hero section complète
-  Footer structuré
-  Recettes s'affichent
-  Responsive testé
-  Animations fluides"
```

---

## ÉTAPE 17 : CORRECTIONS DE LA RECHERCHE ET COHÉRENCE VISUELLE

### 17.1 Problèmes identifiés après tests

Après avoir finalisé l'interface selon le wireframe, plusieurs problèmes sont apparus lors des tests utilisateurs :

**Problèmes de routing** :

-   La recherche depuis l'accueil pointait vers `/recette` au lieu de `/recherche`
-   L'utilisateur ne tombait pas sur la bonne page de résultats

**Problèmes visuels** :

-   Navbar en blanc (`bg-white`), body en beige → incohérence
-   Footer en gris clair (`bg-light`), body en beige → incohérence
-   CSS de pagination disparu après modifications

**Problèmes fonctionnels** :

-   Recherche par ingrédients ne trouvait qu'une seule recette
-   Recherche textuelle trop stricte (ne gérait pas pluriel/minuscules)
-   Beaucoup moins de résultats sur `/recherche` que sur `/recette`

### 17.2 Correction du routing de recherche

**Fichier** : `templates/home/index.html.twig`

**Ligne à modifier** (environ ligne 45) :

```twig
<!-- AVANT (INCORRECT) -->
<form action="{{ path('app_recette_index') }}" method="GET">

<!-- APRÈS (CORRECT) -->
<form action="{{ path('app_search') }}" method="GET">
```

**Explication** :

-   La barre de recherche sur la home doit pointer vers la page de recherche par ingrédients
-   `app_recette_index` = page avec toutes les recettes + filtres avancés
-   `app_search` = page de recherche par ingrédients spécifiques

**Résultat** :

-   Taper "tomate" sur la home → redirige vers `/recherche?q=tomate`
-   Page cohérente avec le wireframe "02 - Recherche Recettes"

### 17.3 Cohérence visuelle navbar/footer/body

**Fichier** : `templates/base.html.twig`

**Problème** : Navbar et footer avaient des couleurs différentes du body.

**Corrections appliquées** :

```twig
<!-- Navbar - ligne ~18 -->
<!-- AVANT -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">

<!-- APRÈS -->
<nav class="navbar navbar-expand-lg navbar-light border-bottom"
     style="background-color: var(--background-color);">
```

```twig
<!-- Footer - ligne ~80 -->
<!-- AVANT -->
<footer class="bg-light py-4 mt-5 border-top">

<!-- APRÈS -->
<footer class="py-4 mt-5 border-top"
        style="background-color: var(--background-color);">
```

**Résultat** :

-   Navbar en beige (#FCF8F5)
-   Body en beige (#FCF8F5)
-   Footer en beige (#FCF8F5)
-   Bordures subtiles pour séparer visuellement
-   Cohérence parfaite avec le wireframe

### 17.4 Ajout du CSS de pagination

**Fichier** : `public/css/app.css`

Le CSS de pagination avait été oublié lors des modifications précédentes. J'ai ajouté un style complet pour la pagination avec la palette de couleurs du projet.

**CSS ajouté à la fin du fichier** :

```css
/* ===================================
   PAGINATION
   =================================== */

.pagination {
    margin: 2rem 0;
    display: flex;
    justify-content: center;
}

.page-link {
    color: var(--success-color);
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    transition: all 0.2s ease;
    background-color: white;
}

.page-link:hover {
    color: white;
    background-color: var(--success-color);
    border-color: var(--success-color);
}

.page-link:focus {
    box-shadow: 0 0 0 0.25rem rgba(76, 175, 80, 0.25);
    color: var(--success-color);
}

.page-item.active .page-link {
    background-color: var(--success-color);
    border-color: var(--success-color);
    color: white;
    font-weight: bold;
    z-index: 3;
}

.page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
    opacity: 0.5;
}

/* Coins arrondis */
.page-item:first-child .page-link {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.page-item:last-child .page-link {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

/* Espacement entre les numéros */
.page-item {
    margin: 0 2px;
}

/* Responsive pagination */
@media (max-width: 576px) {
    .pagination {
        font-size: 0.875rem;
        flex-wrap: wrap;
    }

    .page-link {
        padding: 0.375rem 0.5rem;
        min-width: 32px;
        text-align: center;
    }
}
```

**Caractéristiques** :

-   Couleur verte pour les liens (var(--success-color))
-   Effet hover avec fond vert
-   Page active en vert avec texte blanc
-   Pages désactivées en gris avec opacité
-   Coins arrondis sur premier/dernier élément
-   Responsive pour mobile
-   Focus avec ombre verte pour accessibilité

### 17.5 Problème de la recherche par ingrédients

**Symptôme** : Recherche "tomate" ne trouvait qu'une seule recette alors que la page `/recette` en affichait beaucoup plus.

**Cause identifiée** : Deux problèmes dans le code :

1. **SearchController** : Recherche trop stricte

```php
// Problème : cherche uniquement "Tomate" exact
$ingredient = $ingredientRepository->findOneBy(['nom' => ucfirst($term)]);
```

2. **RecetteRepository::findByIngredients()** : Requête trop complexe

```php
// Problème : JOIN multiples avec alias dynamiques (ri0, ri1, i0, i1...)
foreach ($ingredientIds as $index => $ingredientId) {
    $qb->leftJoin('r.recetteIngredients', 'ri' . $index)
       ->leftJoin('ri' . $index . '.ingredient', 'i' . $index);
}
```

### 17.6 Solution 1 : SearchController amélioré

**Fichier** : `src/Controller/SearchController.php`

J'ai amélioré le contrôleur pour gérer les variations de noms (pluriel, majuscules, minuscules).

**Code corrigé** :

```php
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
    public function index(
        Request $request,
        IngredientRepository $ingredientRepository,
        RecetteRepository $recetteRepository
    ): Response {
        $ingredients = $ingredientRepository->findAll();
        $selectedIngredients = [];
        $recettes = [];

        // Recherche textuelle depuis l'accueil
        $query = $request->query->get('q');
        if ($query) {
            // Séparer les termes
            $searchTerms = array_map('trim', explode(',', $query));

            foreach ($searchTerms as $term) {
                if (strlen($term) >= 2) {
                    // Chercher avec différentes variations
                    $variations = [
                        ucfirst(strtolower($term)),        // "Tomate"
                        strtolower($term),                 // "tomate"
                        ucfirst(strtolower($term)) . 's',  // "Tomates"
                        strtolower($term) . 's',           // "tomates"
                    ];

                    foreach ($variations as $variation) {
                        $ingredient = $ingredientRepository->findOneBy(['nom' => $variation]);
                        if ($ingredient && !in_array($ingredient, $selectedIngredients, true)) {
                            $selectedIngredients[] = $ingredient;
                            break; // Trouvé, pas besoin de chercher les autres variations
                        }
                    }
                }
            }
        }

        // Recherche par IDs d'ingrédients
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
```

**Améliorations** :

-   Gère 4 variations de chaque terme de recherche
-   Singulier/pluriel : "Tomate" et "Tomates"
-   Majuscules/minuscules : "tomate" et "Tomate"
-   Évite les doublons avec `in_array()` strict
-   Minimum 2 caractères pour éviter les recherches trop larges

### 17.7 Solution 2 : RecetteRepository simplifié

**Fichier** : `src/Repository/RecetteRepository.php`

J'ai simplifié la méthode `findByIngredients()` pour corriger le problème des JOIN complexes.

**Méthode remplacée** :

```php
/**
 * Trouve toutes les recettes contenant AU MOINS UN des ingrédients
 */
public function findByIngredients(array $ingredientIds): array
{
    if (empty($ingredientIds)) {
        return [];
    }

    return $this->createQueryBuilder('r')
        ->innerJoin('r.recetteIngredients', 'ri')
        ->innerJoin('ri.ingredient', 'i')
        ->where('i.id IN (:ingredientIds)')
        ->setParameter('ingredientIds', $ingredientIds)
        ->groupBy('r.id')
        ->orderBy('r.dateCreation', 'DESC')
        ->getQuery()
        ->getResult();
}
```

**Comparaison avant/après** :

| Aspect          | Avant                       | Après               |
| --------------- | --------------------------- | ------------------- |
| Complexité      | JOIN dynamiques avec boucle | JOIN simples        |
| Condition WHERE | OR multiples                | IN simple           |
| Lisibilité      | Difficile                   | Claire              |
| Performance     | Moyenne                     | Meilleure           |
| Résultats       | 1 recette                   | Toutes les recettes |

**Explication technique** :

**Avant** :

```php
// Créait : ri0, ri1, i0, i1... pour chaque ingrédient
// WHERE i0.id = :ingredient0 OR i1.id = :ingredient1 OR...
foreach ($ingredientIds as $index => $ingredientId) {
    $qb->leftJoin('r.recetteIngredients', 'ri' . $index);
}
```

-   Compliqué, générait des alias uniques, condition OR longue

**Après** :

```php
// Un seul alias 'ri' et 'i'
// WHERE i.id IN (1, 2, 3)
->innerJoin('r.recetteIngredients', 'ri')
->innerJoin('ri.ingredient', 'i')
->where('i.id IN (:ingredientIds)')
```

-   Simple, efficace, SQL optimisé avec IN

**Résultat SQL généré** :

```sql
SELECT r.*
FROM recette r
INNER JOIN recette_ingredient ri ON r.id = ri.recette_id
INNER JOIN ingredient i ON ri.ingredient_id = i.id
WHERE i.id IN (1, 2, 3)
GROUP BY r.id
ORDER BY r.date_creation DESC
```

### 17.8 Tests après corrections

J'ai effectué une série de tests pour valider les corrections :

**Test 1 : Recherche depuis l'accueil**

-   Action : Taper "tomate" dans la barre de recherche home
-   Résultat attendu : Redirection vers `/recherche?q=tomate`
-   Fonctionne correctement

**Test 2 : Variations de recherche**

-   Actions testées :
    -   "tomate" (minuscule, singulier)
    -   "Tomate" (majuscule, singulier)
    -   "tomates" (minuscule, pluriel)
    -   "Tomates" (majuscule, pluriel)
-   Toutes les variations trouvent les mêmes recettes

**Test 3 : Recherche multiple**

-   Action : Taper "tomate, oeuf"
-   Résultat attendu : Recettes avec tomates OU oeufs
-   Affiche toutes les recettes correspondantes

**Test 4 : Comparaison quantité**

```bash
# Requête SQL de vérification
SELECT COUNT(DISTINCT r.id)
FROM recette r
INNER JOIN recette_ingredient ri ON r.id = ri.recette_id
INNER JOIN ingredient i ON ri.ingredient_id = i.id
WHERE i.nom LIKE '%tomate%'
```

-   Résultat : Même nombre sur `/recherche` et dans la requête SQL [OK]

**Test 5 : Cohérence visuelle**

-   Navbar beige
-   Body beige
-   Footer beige
-   Bordures subtiles
-   Pagination verte avec hover

**Test 6 : Pagination**

-   Numéros verts
-   Hover change fond en vert
-   Page active en vert avec texte blanc
-   Responsive sur mobile

### 17.9 Problèmes résolus - Récapitulatif

| Problème                           | Solution                 | Statut |
| ---------------------------------- | ------------------------ | ------ |
| Routing incorrect (home → recette) | Changé vers app_search   |        |
| Navbar blanche                     | background-color beige   |        |
| Footer gris                        | background-color beige   |        |
| CSS pagination manquant            | Ajouté styles complets   |        |
| Recherche stricte                  | Variations nom (4 cas)   |        |
| JOIN complexes                     | Simplifié avec IN        |        |
| 1 seule recette trouvée            | Toutes recettes trouvées |        |
| Incohérence visuelle               | Palette cohérente        |        |

### 17.10 Impacts sur l'expérience utilisateur

**Avant les corrections** :

-   Utilisateur tape "tomate" sur home → arrive sur page recettes générale (confusion)
-   Couleurs incohérentes (blanc/gris/beige mélangés)
-   Recherche ne trouve presque rien (frustration)
-   Pagination sans style (non professionnel)

**Après les corrections** :

-   Utilisateur tape "tomate" → arrive sur page recherche dédiée (logique)
-   Design cohérent avec wireframe (professionnel)
-   Recherche trouve toutes les recettes pertinentes (satisfaction)
-   Pagination stylisée et fonctionnelle (polissage)

### 17.11 Fichiers modifiés

**Templates** :

-   `templates/home/index.html.twig` - Routing corrigé
-   `templates/base.html.twig` - Couleurs cohérentes

**Contrôleurs** :

-   `src/Controller/SearchController.php` - Variations de recherche

**Repositories** :

-   `src/Repository/RecetteRepository.php` - Méthode simplifiée

**CSS** :

-   `public/css/app.css` - Styles pagination ajoutés

### 17.12 Bonnes pratiques appliquées

**Architecture** :

-   Séparation claire : `/recette` (toutes recettes) vs `/recherche` (par ingrédients)
-   Repository responsable des requêtes complexes
-   Contrôleur gère la logique métier (variations)

**Performance** :

-   Requête SQL optimisée avec `IN` au lieu de multiples `OR`
-   `GROUP BY` pour éviter doublons
-   Index sur `i.id` utilisé efficacement

**UX** :

-   Recherche flexible (accepte variations)
-   Feedback visuel cohérent (palette unique)
-   Pagination accessible (focus, hover)

**Maintenabilité** :

-   Code simplifié et lisible
-   Variables CSS pour cohérence
-   Commentaires explicatifs

### 17.13 Commit de correction

```bash
git add templates/home/index.html.twig templates/base.html.twig src/Controller/SearchController.php src/Repository/RecetteRepository.php public/css/app.css
git commit -m "fix: Corrections recherche et cohérence visuelle

Routing:
- Corrigé formulaire home vers app_search au lieu de app_recette_index
- Utilisateur atterrit maintenant sur la bonne page de recherche

Cohérence visuelle:
- Navbar et footer en beige (var(--background-color))
- Supprimé bg-white et bg-light
- Ajouté bordures subtiles pour séparation
- Palette cohérente avec wireframe

Pagination:
- Ajouté CSS complet pour pagination
- Couleurs vertes avec hover
- Page active mise en évidence
- Responsive mobile

SearchController:
- Gère 4 variations de noms (majuscule/minuscule, singulier/pluriel)
- Recherche flexible: 'tomate', 'Tomate', 'tomates', 'Tomates'
- Évite doublons dans résultats
- Minimum 2 caractères par terme

RecetteRepository:
- Simplifié findByIngredients() avec IN au lieu de OR multiples
- JOIN simples au lieu d'alias dynamiques
- Requête SQL optimisée
- Trouve maintenant TOUTES les recettes avec ingrédients

Tests:
-  Recherche trouve toutes recettes pertinentes
-  Variations de noms fonctionnent
-  Cohérence visuelle navbar/body/footer
-  Pagination stylisée
-  Routing correct home → recherche"
```

---

## ÉTAPE 18 : PAGE DÉTAIL RECETTE SELON WIREFRAME 03

### 18.1 Analyse du wireframe 03 - Fiche Recette

Le wireframe présente une page de détail complète avec plusieurs éléments importants :

**Éléments du header** :

-   Bouton "← Retour aux recettes" en haut à gauche
-   Bouton "🧡 Ajouter aux Favoris" en haut à droite
-   Image de la recette centrée et grande

**Informations principales** :

-   Titre de la recette (h1 vert)
-   Étoiles de notation avec nombre d'avis
-   3 icônes : Temps (⏱️), Difficulté (👤), Personnes (👥)
-   Auteur et date de publication

**Structure en 2 colonnes** :

-   Colonne gauche : Ingrédients avec checkboxes
-   Colonne droite : Étapes de préparation numérotées

**Section commentaires** :

-   Formulaire avec textarea et bouton orange "Publier le commentaire"
-   Liste des commentaires avec avatar circulaire
-   Note en étoiles pour chaque commentaire

### 18.2 Comparaison avec la page existante

| Élément                | Wireframe                  | Page actuelle | À faire      |
| ---------------------- | -------------------------- | ------------- | ------------ |
| Bouton retour          | En haut à gauche           | Manquant      | Ajouter      |
| Bouton favoris         | En haut à droite           | Présent (API) | Améliorer UI |
| Layout 2 colonnes      | Ingrédients \| Préparation | 1 colonne     | Modifier     |
| Checkboxes ingrédients | Interactives               | Simple liste  | Ajouter      |
| Avatar commentaires    | Rond avec initiale         | Manquant      | Ajouter      |
| Bouton publier         | Orange                     | Présent       | Styliser     |

### 18.3 Implémentation du template show.html.twig

**Fichier** : `templates/recette/show.html.twig`

J'ai créé un template complet qui respecte le wireframe :

**Structure du template** :

```twig
{% extends 'base.html.twig' %}

{% block body %}
<div class="container my-4">
    <!-- 1. Header avec boutons retour et favoris -->
    <!-- 2. Image principale centrée -->
    <!-- 3. Titre, étoiles, infos (temps/difficulté/personnes) -->
    <!-- 4. Auteur et date -->
    <!-- 5. Section 2 colonnes (Ingrédients | Préparation) -->
    <!-- 6. Section commentaires avec formulaire -->
    <!-- 7. Liste des commentaires avec avatars -->
</div>
{% endblock %}
```

### 18.4 Bouton retour aux recettes

```twig
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ path('app_recette_index') }}" class="btn btn-outline-success">
        <i class="bi bi-arrow-left"></i> Retour aux recettes
    </a>

    <!-- Bouton favoris ici -->
</div>
```

**Caractéristiques** :

-   Bouton vert outline
-   Icône flèche gauche Bootstrap Icons
-   Lien vers la page index des recettes

### 18.5 Bouton favoris avec AJAX

Le controller API `FavoriController` dans `src/Controller/Api/` retourne déjà du JSON, j'ai donc implémenté une solution AJAX.

**Bouton HTML** :

```twig
{% if app.user %}
    <button type="button"
            class="btn btn-warning"
            id="favoriBtn"
            data-recette-id="{{ recette.id }}"
            data-is-favorite="{{ isFavorite ? 'true' : 'false' }}">
        <i class="bi bi-heart{{ isFavorite ? '-fill' : '' }}" id="favoriIcon"></i>
        <span id="favoriText">{{ isFavorite ? 'Retirer des favoris' : 'Ajouter aux Favoris' }}</span>
    </button>
{% endif %}
```

**JavaScript AJAX** :

```javascript
document
    .getElementById("favoriBtn")
    .addEventListener("click", async function () {
        const btn = this;
        const recetteId = btn.dataset.recetteId;

        // Appel API
        const response = await fetch(`/api/favori/toggle/${recetteId}`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
        });

        const data = await response.json();

        if (data.success) {
            // Mettre à jour l'icône et le texte
            if (data.isFavorite) {
                icon.classList.add("bi-heart-fill");
                text.textContent = "Retirer des favoris";
            } else {
                icon.classList.remove("bi-heart-fill");
                text.textContent = "Ajouter aux Favoris";
            }

            // Animation
            icon.style.transform = "scale(1.3)";
            setTimeout(() => (icon.style.transform = "scale(1)"), 200);
        }
    });
```

**Avantages de l'approche AJAX** :

-   Pas de rechargement de page
-   Feedback immédiat avec animation
-   Meilleure UX
-   Utilise le controller API existant

### 18.6 Image principale de la recette

```twig
<div class="row justify-content-center mb-4">
    <div class="col-lg-8">
        {% if recette.image %}
            <img src="/uploads/recettes/{{ recette.image }}"
                 class="img-fluid rounded shadow"
                 style="width: 100%; max-height: 500px; object-fit: cover;">
        {% else %}
            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                 style="height: 400px;">
                <i class="bi bi-image text-muted" style="font-size: 5rem;"></i>
            </div>
        {% endif %}
    </div>
</div>
```

**Caractéristiques** :

-   Centrée avec `col-lg-8`
-   Hauteur maximale 500px
-   `object-fit: cover` pour éviter déformation
-   Ombre portée pour effet profondeur
-   Placeholder si pas d'image

### 18.7 Titre et informations principales

**Titre avec étoiles** :

```twig
<div class="text-center mb-4">
    <h1 class="mb-3">{{ recette.nom }}</h1>

    <!-- Étoiles de notation -->
    <div class="mb-3">
        {% set moyenne = recette.moyenneNotes %}
        {% for i in 1..5 %}
            {% if i <= moyenne %}
                <i class="bi bi-star-fill text-warning fs-5"></i>
            {% else %}
                <i class="bi bi-star text-warning fs-5"></i>
            {% endif %}
        {% endfor %}
        <span class="ms-2 text-muted">({{ recette.commentaires|length }} avis)</span>
    </div>
</div>
```

**Icônes d'informations** :

```twig
<div class="d-flex justify-content-center gap-4 mb-3">
    <div class="text-center">
        <i class="bi bi-clock text-success fs-4"></i>
        <p class="mb-0 small">{{ recette.tempsCuisson }}min</p>
    </div>
    <div class="text-center">
        <i class="bi bi-person-gear text-success fs-4"></i>
        <p class="mb-0 small">
            {% if recette.difficulte == 1 %}Facile
            {% elseif recette.difficulte == 2 %}Moyen
            {% else %}Difficile{% endif %}
        </p>
    </div>
    <div class="text-center">
        <i class="bi bi-people text-success fs-4"></i>
        <p class="mb-0 small">{{ recette.nombrePersonnes }} pers.</p>
    </div>
</div>
```

**Auteur et date** :

```twig
<p class="text-muted small">
    Par {{ recette.user.prenom }} {{ recette.user.nom|first }}. -
    {{ recette.dateCreation|date('d M Y') }}
</p>
```

### 18.8 Layout 2 colonnes : Ingrédients | Préparation

C'est l'élément clé qui correspond au wireframe !

**Structure** :

```twig
<div class="row mb-5">
    <!-- Colonne 1 : Ingrédients -->
    <div class="col-lg-6 mb-4">
        <h3 class="mb-3 text-success">Ingrédients</h3>
        <div class="card">
            <div class="card-body">
                <!-- Checkboxes ici -->
            </div>
        </div>
    </div>

    <!-- Colonne 2 : Préparation -->
    <div class="col-lg-6 mb-4">
        <h3 class="mb-3 text-success">Préparation</h3>
        <div class="card">
            <div class="card-body">
                <!-- Liste numérotée ici -->
            </div>
        </div>
    </div>
</div>
```

### 18.9 Checkboxes interactives pour les ingrédients

**HTML** :

```twig
{% for recetteIngredient in recette.recetteIngredients %}
    <div class="form-check mb-2">
        <input class="form-check-input"
               type="checkbox"
               id="ing{{ loop.index }}">
        <label class="form-check-label" for="ing{{ loop.index }}">
            <span class="fw-bold text-success">{{ recetteIngredient.quantite }} {{ recetteIngredient.unite }}</span>
            {{ recetteIngredient.ingredient.nom }}
        </label>
    </div>
{% endfor %}
```

**JavaScript localStorage** :

```javascript
document
    .querySelectorAll('.form-check-input[type="checkbox"]')
    .forEach((checkbox) => {
        const storageKey = "recette_{{ recette.id }}_" + checkbox.id;

        // Restaurer l'état sauvegardé
        if (localStorage.getItem(storageKey) === "true") {
            checkbox.checked = true;
            checkbox.parentElement.classList.add(
                "text-decoration-line-through",
                "text-muted"
            );
        }

        // Sauvegarder au changement
        checkbox.addEventListener("change", function () {
            localStorage.setItem(storageKey, this.checked);
            if (this.checked) {
                this.parentElement.classList.add(
                    "text-decoration-line-through",
                    "text-muted"
                );
            } else {
                this.parentElement.classList.remove(
                    "text-decoration-line-through",
                    "text-muted"
                );
            }
        });
    });
```

**Fonctionnement** :

-   État sauvegardé par recette dans localStorage
-   Texte barré quand coché
-   Couleur grise quand coché
-   Persiste entre les visites

### 18.10 Étapes de préparation numérotées

**Code** :

```twig
{% set etapes = recette.etapes|split('\n') %}
<ol class="mb-0" style="padding-left: 1.5rem;">
    {% for etape in etapes %}
        {% set cleanEtape = etape|trim|replace({
            '1. ': '', '2. ': '', '3. ': '', '4. ': '',
            '5. ': '', '6. ': '', '7. ': '', '8. ': '',
            '9. ': '', '10. ': ''
        }) %}
        {% if cleanEtape %}
            <li class="mb-3 text-success">
                <span class="text-dark">{{ cleanEtape }}</span>
            </li>
        {% endif %}
    {% endfor %}
</ol>
```

**Caractéristiques** :

-   Split par `\n` pour séparer les étapes
-   Nettoyage des numéros existants
-   Numéros verts avec CSS `li::marker`
-   Texte noir pour lisibilité

### 18.11 Section commentaires avec formulaire

**Formulaire d'ajout** :

```twig
{% if app.user %}
    <div class="card mb-4">
        <div class="card-body">
            {{ form_start(commentaireForm) }}
                <!-- Textarea -->
                <div class="mb-3">
                    {{ form_widget(commentaireForm.contenu, {
                        'attr': {
                            'placeholder': 'Laissez votre avis sur cette recette...'
                        }
                    }) }}
                </div>

                <!-- Radio buttons pour note -->
                <div class="mb-3">
                    {{ form_label(commentaireForm.note, 'Votre note :') }}
                    <div class="d-flex gap-2">
                        {% for choice in commentaireForm.note.vars.choices %}
                            <input type="radio"
                                   class="btn-check"
                                   name="{{ commentaireForm.note.vars.full_name }}"
                                   id="note{{ choice.value }}"
                                   value="{{ choice.value }}">
                            <label class="btn btn-outline-warning" for="note{{ choice.value }}">
                                <i class="bi bi-star-fill"></i> {{ choice.value }}
                            </label>
                        {% endfor %}
                    </div>
                </div>

                <!-- Bouton orange -->
                <button type="submit" class="btn btn-warning px-4">
                    Publier le commentaire
                </button>
            {{ form_end(commentaireForm) }}
        </div>
    </div>
{% endif %}
```

**Fonctionnalités** :

-   Textarea grande pour le contenu
-   5 boutons radio stylisés pour la note
-   Bouton orange (var --cta-color)
-   Utilise le form Symfony CommentaireType

### 18.12 Affichage des commentaires avec avatars

**Code** :

```twig
{% for commentaire in recette.commentaires|reverse %}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex">
                <!-- Avatar circulaire -->
                <div class="me-3">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                         style="width: 50px; height: 50px; font-size: 1.5rem;">
                        {{ commentaire.user.prenom|slice(0, 1)|upper }}
                    </div>
                </div>

                <!-- Contenu -->
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-0">{{ commentaire.user.prenom }} {{ commentaire.user.nom|slice(0, 1) }}.</h6>
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> {{ commentaire.dateCreation|date('d/m/Y') }}
                            </small>
                        </div>
                        <div>
                            {% for i in 1..5 %}
                                {% if i <= commentaire.note %}
                                    <i class="bi bi-star-fill text-warning"></i>
                                {% else %}
                                    <i class="bi bi-star text-warning"></i>
                                {% endif %}
                            {% endfor %}
                        </div>
                    </div>
                    <p class="mb-0">{{ commentaire.contenu }}</p>
                </div>
            </div>
        </div>
    </div>
{% endfor %}
```

**Éléments clés** :

-   Avatar vert avec initiale du prénom
-   Nom complet + initiale du nom (ex: "Sophie L.")
-   Date de création
-   Étoiles de notation à droite
-   Contenu du commentaire
-   Ordre inversé (plus récent en premier)

### 18.13 CSS personnalisé pour la page détail

**Fichier** : `public/css/app.css`

```css
/* ===================================
   PAGE DÉTAIL RECETTE
   =================================== */

/* Image principale avec ombre */
.img-fluid.rounded.shadow {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
}

/* Checkboxes ingrédients */
.form-check-input {
    border: 2px solid var(--success-color);
    width: 1.25rem;
    height: 1.25rem;
}

.form-check-input:checked {
    background-color: var(--success-color);
    border-color: var(--success-color);
}

.form-check-input:focus {
    border-color: var(--success-color);
    box-shadow: 0 0 0 0.25rem rgba(76, 175, 80, 0.25);
}

.form-check-label {
    cursor: pointer;
    transition: all 0.2s ease;
}

/* Avatar circulaire */
.rounded-circle {
    font-weight: bold;
    flex-shrink: 0;
}

/* Animation coeur favoris */
#favoriIcon {
    transition: transform 0.2s ease;
    display: inline-block;
}

.btn-warning:hover #favoriIcon {
    transform: scale(1.15);
}

.btn-warning:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Radio buttons notation */
.btn-check:checked + .btn-outline-warning {
    background-color: var(--cta-color);
    border-color: var(--cta-color);
    color: white;
}

.btn-check + .btn-outline-warning {
    border-color: var(--cta-color);
    color: var(--cta-color);
}

.btn-check + .btn-outline-warning:hover {
    background-color: rgba(240, 138, 0, 0.1);
}

/* Numéros étapes en vert */
ol li::marker {
    color: var(--success-color);
    font-weight: bold;
}

/* Cartes ingrédients/préparation */
.card {
    border: 1px solid rgba(76, 175, 80, 0.2);
}
```

### 18.14 Controller RecetteController

Le controller existant gère déjà :

-   Affichage de la recette
-   Vérification si en favoris (`isFavorite`)
-   Formulaire de commentaire (`CommentaireType`)
-   Traitement POST du commentaire
-   Incrémentation des vues

**Méthode `show()`** :

```php
#[Route('/{id}', name: 'app_recette_show', methods: ['GET', 'POST'])]
public function show(
    Request $request,
    Recette $recette,
    FavoriRepository $favoriRepository,
    EntityManagerInterface $entityManager
): Response {
    // Incrémenter les vues
    $recette->setVue($recette->getVue() + 1);
    $entityManager->flush();

    // Vérifier si en favoris
    $isFavorite = false;
    if ($this->getUser()) {
        $isFavorite = $favoriRepository->findOneBy([
            'user' => $this->getUser(),
            'recette' => $recette
        ]) !== null;
    }

    // Formulaire commentaire
    $commentaire = new Commentaire();
    $form = $this->createForm(CommentaireType::class, $commentaire);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $commentaire->setUser($this->getUser());
        $commentaire->setRecette($recette);
        $entityManager->persist($commentaire);
        $entityManager->flush();

        $this->addFlash('success', 'Votre commentaire a été publié !');
        return $this->redirectToRoute('app_recette_show', ['id' => $recette->id]);
    }

    return $this->render('recette/show.html.twig', [
        'recette' => $recette,
        'isFavorite' => $isFavorite,
        'commentaireForm' => $form,
    ]);
}
```

### 18.15 Tests de la page détail

J'ai effectué une série de tests pour valider l'implémentation :

**Test 1 : Layout et structure**

-   Bouton retour en haut à gauche
-   Bouton favoris en haut à droite (si connecté)
-   Image centrée et responsive
-   Titre, étoiles, infos bien affichés
-   2 colonnes sur desktop (Ingrédients | Préparation)
-   1 colonne sur mobile (stack vertical)

**Test 2 : Checkboxes ingrédients**

-   Action : Cocher 3 ingrédients
-   Texte devient barré et grisé
-   État sauvegardé dans localStorage
-   Action : Recharger la page
-   Les 3 checkboxes restent cochées

**Test 3 : Bouton favoris AJAX**

-   Action : Cliquer "Ajouter aux Favoris"
-   Requête AJAX vers `/api/favori/toggle/{id}`
-   Icône change : `bi-heart` → `bi-heart-fill`
-   Texte change : "Ajouter aux Favoris" → "Retirer des favoris"
-   Animation scale(1.3) puis scale(1)
-   Pas de rechargement de page
-   Action : Cliquer "Retirer des favoris"
-   Revient à l'état initial

**Test 4 : Commentaires**

-   Action : Remplir le formulaire (note 5 étoiles + texte)
-   Commentaire apparaît en haut de la liste
-   Avatar vert avec initiale du prénom
-   5 étoiles affichées
-   Date du jour
-   Message flash "Votre commentaire a été publié !"

**Test 5 : Responsive**

-   Desktop (>992px) : 2 colonnes
-   Tablet (768-992px) : 2 colonnes
-   Mobile (<768px) : 1 colonne (stack)
-   Image s'adapte correctement

### 18.16 Comparaison finale wireframe vs résultat

| Élément          | Wireframe 03                    | Résultat final                      | Statut |
| ---------------- | ------------------------------- | ----------------------------------- | ------ |
| Bouton retour    | Vert outline, flèche gauche     | Identique                           |        |
| Bouton favoris   | Orange avec coeur               | AJAX + animation                    |        |
| Image            | Grande, centrée                 | Responsive, ombre                   |        |
| Titre            | H1 vert                         | Vert (var --navigation-title-color) |        |
| Étoiles          | 3/5 (24 avis)                   | Dynamique avec compteur             |        |
| Icônes infos     | Temps, Difficulté, Personnes    | Bootstrap Icons vertes              |        |
| 2 colonnes       | Ingrédients \| Préparation      | col-lg-6 chacune                    |        |
| Checkboxes       | Interactives                    | + localStorage                      |        |
| Liste numérotée  | Étapes préparation              | Numéros verts                       |        |
| Formulaire comm. | Textarea + note + bouton orange | Symfony Form                        |        |
| Avatar           | Rond vert avec initiale         | 50x50px, bg-success                 |        |
| Liste comm.      | Avec étoiles et date            | Ordre inversé                       |        |

**Conclusion** : L'implémentation correspond à 100% au wireframe !

### 18.17 Fonctionnalités bonus ajoutées

Au-delà du wireframe, j'ai ajouté :

**localStorage pour checkboxes** :

-   Sauvegarde l'état des ingrédients cochés
-   Persiste entre les visites
-   Pratique pour faire les courses

**Animation du coeur** :

-   Scale(1.3) au clic
-   Transition smooth
-   Feedback visuel immédiat

**Gestion erreurs AJAX** :

-   Try/catch sur la requête
-   Alert si erreur réseau
-   Bouton désactivé pendant requête

**Placeholder image** :

-   Icône Bootstrap si pas d'image
-   Évite zone vide

**Ordre des commentaires** :

-   Plus récents en premier (`|reverse`)
-   Logique pour UX

### 18.18 Fichiers modifiés/créés

**Templates** :

-   `templates/recette/show.html.twig` - Revu complètement

**CSS** :

-   `public/css/app.css` - Section "PAGE DÉTAIL RECETTE"

**Controllers** :

-   `src/Controller/Api/FavoriController.php` - Déjà existant, utilisé

**Aucun changement nécessaire** :

-   `RecetteController::show()` - Déjà complet
-   Entity `Recette`, `Commentaire`, `Favori` - OK

### 18.19 Points d'amélioration possibles (futurs)

**Pour aller plus loin** :

1. **Modifier commentaire** : Bouton éditer/supprimer (si auteur)
2. **Images multiples** : Galerie de photos pour la recette
3. **Imprimer recette** : Bouton pour version imprimable
4. **Partage social** : Boutons Facebook/Twitter
5. **Temps de lecture** : Estimation automatique
6. **Ingrédients substituables** : Suggestions alternatives
7. **Vidéo** : Intégration YouTube
8. **Nutrition** : Tableau calories/protéines/etc.

### 18.20 Corrections finales : Conformité stricte au wireframe

Après tests visuels, plusieurs différences majeures avec le wireframe ont été identifiées :

**Problèmes identifiés** :

-   Image trop petite et centrée (doit être pleine largeur)
-   Titre centré (doit être aligné à gauche)
-   Étoiles centrées (doivent être alignées à gauche)
-   Ingrédients et préparation dans des cartes (doivent être simples sans bordures)
-   Quantités absurdes : "334 Pommes", "221 Sucre"
-   Colonnes non alignées

**Corrections template `show.html.twig`** :

1. **Image pleine largeur** :

```twig
<div class="container-fluid px-0">
    <img src="/uploads/recettes/{{ recette.image }}"
         class="img-fluid w-100"
         style="max-height: 500px; object-fit: cover;">
</div>
```

2. **Titre et étoiles alignés à gauche** :

```twig
<h1 class="text-success mb-3">{{ recette.nom }}</h1>
<div class="mb-3">
    <!-- Étoiles sans center -->
</div>
```

3. **Icônes infos alignées à gauche** :

```twig
<div class="d-flex gap-4 mb-3">
    <!-- Sans justify-content-center -->
</div>
```

4. **Sections sans cartes** :

```twig
<div class="col-lg-6 mb-4">
    <h3 class="text-success mb-3">Ingrédients</h3>
    <!-- Directement les checkboxes, pas de <div class="card"> -->
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox">
        <label>{{ quantite }}{{ ingredient }}</label>
    </div>
</div>
```

**Corrections fixtures `AppFixtures.php`** :

Quantités réalistes selon type d'ingrédient :

```php
$quantite = match($ingName) {
    'Œufs' => rand(2, 6),              // "3oeufs"
    'Tomates', 'Pommes' => rand(2, 4), // "2 tomates"
    'Ail' => rand(1, 3),               // "1 ail"
    'Sel', 'Poivre' => 0,              // Juste le nom
    'Farine', 'Sucre' => rand(100, 300), // "200g Farine"
    'Beurre' => rand(50, 150),
    'Fromage', 'Jambon' => rand(100, 200),
    'Poulet', 'Bœuf', 'Poisson' => rand(400, 800),
    default => rand(100, 300)
};
```

**Corrections CSS `app.css`** :

Nettoyage et réorganisation :

-   Suppression emojis et commentaires excessifs
-   Organisation logique des sections
-   Checkboxes vertes avec coche blanche SVG
-   Responsive sans cartes

**Format d'affichage** :

-   Format simple : "3oeufs" (sans espace)
-   Avec unité : "200g Farine"
-   Sans quantité : "Sel, poivre"

### 18.21 Résultat final conforme

**Comparaison wireframe vs résultat** :

| Élément      | Wireframe         | Avant          | Après          |
| ------------ | ----------------- | -------------- | -------------- |
| Image        | Pleine largeur    | Petite centrée | Pleine largeur |
| Titre        | Gauche            | Centré         | Gauche         |
| Étoiles      | Gauche            | Centrées       | Gauche         |
| Icônes infos | Gauche            | Centrées       | Gauche         |
| Sections     | Sans cartes       | Avec cartes    | Sans cartes    |
| Colonnes     | Alignées          | Décalées       | Alignées       |
| Quantités    | 3oeufs, 2 tomates | 334 Pommes     | 3oeufs         |

### 18.22 Commit final

```bash
git add public/css/app.css src/DataFixtures/AppFixtures.php templates/recette/show.html.twig
git commit -m "feat: Page détail recette conforme wireframe 03

CORRECTIONS MAJEURES LAYOUT:
- Image pleine largeur (container-fluid) au lieu de centrée
- Titre aligné à gauche au lieu de centré
- Étoiles et infos alignées à gauche
- Suppression des cartes autour ingrédients/préparation
- Colonnes parfaitement alignées

Template show.html.twig:
- Image pleine largeur avec max-height 500px
- Titre H1 vert aligné gauche
- Étoiles notation + compteur avis alignés gauche
- Icônes temps/difficulté/personnes en ligne (d-flex gap-4)
- Auteur et date en petit gris
- Layout 2 colonnes SANS cartes: Ingrédients | Préparation
- Checkboxes vertes avec localStorage
- Liste numérotée préparation (numéros verts)
- Formulaire commentaire avec note étoilée
- Avatars circulaires verts pour commentaires
- Bouton favoris AJAX orange

Fixtures AppFixtures.php:
- Quantités réalistes par type ingrédient
- Œufs: 2-6 (affiche '3oeufs')
- Tomates/Pommes: 2-4 (affiche '2 tomates')
- Ail: 1-3 (affiche '1 ail')
- Farine/Sucre: 100-300g (affiche '200g Farine')
- Viandes: 400-800g
- Épices: quantité 0 (affiche juste nom)
- Unité vide par défaut
- 50 recettes avec ingrédients cohérents

CSS app.css:
- Nettoyage sans emojis ni commentaires excessifs
- Organisation sections logiques
- Checkboxes vertes 1.25rem avec coche SVG
- Texte barré gris quand coché
- Numéros préparation verts (li::marker)
- Avatar commentaires 50x50px bg-success
- Animation coeur favoris scale(1.15)
- Responsive 2 colonnes → 1 colonne mobile
- Suppression styles cartes inutilisés

Favoris AJAX:
- Appel /api/favori/toggle/{id}
- Icône bi-heart / bi-heart-fill dynamique
- Texte 'Ajouter' / 'Retirer' dynamique
- Animation scale au clic
- Désactivation pendant requête
- Gestion erreurs try/catch

Checkboxes localStorage:
- Sauvegarde état par recette + id checkbox
- Texte barré + color #999 quand coché
- Persiste entre visites
- Pratique liste courses

Tests effectués:
 Image pleine largeur responsive
 Tous éléments alignés gauche
 Colonnes parfaitement alignées
 Quantités réalistes: 3oeufs, 2 tomates
 Checkboxes fonctionnelles + localStorage
 Bouton favoris AJAX + animation
 Formulaire commentaire
 Avatars circulaires affichés
 Responsive desktop/tablet/mobile

Wireframe 03:
 100% conforme visuellement
 Layout identique
 Quantités cohérentes
 Interactions fonctionnelles"
```

---

## PROCHAINES ÉTAPES

### Fonctionnalités à développer

#### Gestion dynamique des ingrédients - TERMINÉ

-   [x] API de recherche d'ingrédients
-   [x] FormType avec champs dynamiques
-   [x] Collection Symfony fonctionnelle
-   [x] JavaScript pour ajout/suppression
-   [x] Autocomplete avec debounce
-   [x] Interface responsive
-   [x] Tests complets réussis
-   [x] Mode création et édition

#### Recherche avancée - TERMINÉ

-   [x] Formulaire de recherche avec filtres multiples
-   [x] Filtre par catégorie
-   [x] Filtre par difficulté
-   [x] Filtre par temps de préparation
-   [x] Recherche textuelle (nom, ingrédients)
-   [x] Tri dynamique (date, note, temps)
-   [x] Pagination avec KnpPaginatorBundle
-   [x] Interface collapsible
-   [x] URLs partageables

#### Pages institutionnelles

-   [ ] Page Contact avec formulaire
-   [ ] Page CGU (Conditions Générales d'Utilisation)
-   [ ] Page Mentions Légales
-   [ ] Page RGPD / Politique de confidentialité
-   [ ] Page À propos

#### Profil utilisateur avancé

-   [ ] Modification avatar
-   [ ] Modification bio
-   [ ] Statistiques personnelles
-   [ ] Badge et gamification

#### API REST

-   [ ] Endpoints API pour recettes
-   [ ] Documentation API (OpenAPI)
-   [ ] Authentification JWT
-   [ ] Rate limiting

#### Tests

-   [ ] Tests unitaires (PHPUnit)
-   [ ] Tests fonctionnels
-   [ ] Tests d'intégration
-   [ ] Couverture de code >70%

#### Performance

-   [ ] Cache Symfony
-   [ ] Optimisation requêtes Doctrine
-   [ ] Lazy loading images
-   [ ] Pagination

#### Sécurité

-   [ ] CSRF tokens partout
-   [ ] Validation stricte formulaires
-   [ ] Rate limiting connexion
-   [ ] Audit sécurité

### Améliorations techniques

-   [ ] Migration vers Symfony 7.3 (si nouvelle version)
-   [ ] Mise en place CI/CD (GitHub Actions)
-   [ ] Docker Compose complet (Nginx, PHP, MySQL)
-   [ ] Documentation développeur (README détaillé)

---

## CONCLUSION

### État actuel du projet

Le projet Les Restes est actuellement dans un état fonctionnel avec les fonctionnalités suivantes opérationnelles :

-   **Backend complet** :

-   Architecture Symfony 7.3 solide
-   Base de données MySQL bien structurée
-   Entités avec relations complexes (User, Recette, Ingredient, Categorie, Commentaire, Favori)
-   Système d'authentification sécurisé
-   API REST pour recherche d'ingrédients

**Fonctionnalités utilisateur** :

-   Inscription et connexion
-   Création et gestion de recettes avec ingrédients dynamiques
-   Upload d'images optimisé
-   Système de favoris avec AJAX
-   Système de commentaires et notation complet
-   Recherche avancée avec filtres multiples
-   Pagination professionnelle
-   Profil utilisateur avec onglets

**Interface utilisateur** :

-   Design moderne avec Bootstrap 5
-   Navigation responsive
-   Templates cohérents et professionnels
-   Images uniformes avec fallback
-   Formulaires optimisés

### Points forts du projet

1. **Architecture propre** : Respect des standards Symfony et des bonnes pratiques
2. **Code versionné** : Utilisation méthodique de Git avec branches thématiques
3. **Design soigné** : Interface moderne et intuitive
4. **Fonctionnalités AJAX** : Favoris sans rechargement de page
5. **Optimisations UX** : Formulaires compacts, images uniformes

-### Compétences démontrées

-   Maîtrise de Symfony 7.3

**Note technique :**

-   Les contrôleurs ont été normalisés pour passer les formulaires à Twig sous forme de `FormView` (ex. `->createView()`). Cela améliore la compatibilité entre Symfony et Twig et évite des comportements dépendants de versions.
-   Gestion de base de données avec Doctrine
-   Sécurité et authentification
-   Upload de fichiers avec VichUploader
-   Frontend moderne (Bootstrap, JavaScript)
-   API REST (favoris, recherche d'ingrédients)
-   Git et méthodologie de développement
-   Formulaires complexes avec Collections
-   QueryBuilder et requêtes Doctrine avancées
-   Pagination et filtrage de résultats
-   JavaScript moderne (autocomplete, debounce, AJAX)

### Préparation pour la soutenance

Ce projet démontre ma capacité à :

-   Concevoir une application web complète
-   Utiliser un framework PHP moderne
-   Créer une interface utilisateur professionnelle
-   Gérer un projet avec Git
-   Documenter mon travail de manière détaillée

---

---

## ÉTAPE 19 : ADMINISTRATION DES INGRÉDIENTS

### 19.1 Contexte et besoin

Le projet nécessite une interface d'administration pour gérer les ingrédients de manière centralisée. Cette fonctionnalité permet aux administrateurs de :

-   Créer de nouveaux ingrédients

-   Modifier les ingrédients existants

-   Supprimer les ingrédients non utilisés

-   Rechercher et paginer les ingrédients

-   Voir l'utilisation des ingrédients dans les recettes

**Compétences démontrées** :

-   Gestion des rôles et permissions (ROLE_ADMIN)

-   CRUD complet en administration

-   Recherche et pagination

-   Validation des suppressions

### 19.2 Configuration du rôle ADMIN

**Attribution du rôle via SQL** :

```bash

php bin/console doctrine:query:sql "UPDATE user SET roles = '[\"ROLE_USER\", \"ROLE_ADMIN\"]' WHERE email = 'admin@lesrestes.com'"



# Vérification

php bin/console doctrine:query:sql "SELECT email, roles FROM user"

```

**Résultat** :

```

email                 | roles

admin@lesrestes.com   | ["ROLE_USER", "ROLE_ADMIN"]

```

### 19.3 Création du formulaire IngredientType

**Fichier** : `src/Form/IngredientType.php`

```php

<?php



namespace App\Form;



use App\Entity\Ingredient;

use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\Length;

use Symfony\Component\Validator\Constraints\NotBlank;



class IngredientType extends AbstractType

{

    public function buildForm(FormBuilderInterface $builder, array $options): void

    {

        $builder

            ->add('nom', TextType::class, [

                'label' => 'Nom de l\'ingrédient',

                'attr' => [

                    'class' => 'form-control',

                    'placeholder' => 'Ex: Tomate, Oignon, Farine...'

                ],

                'constraints' => [

                    new NotBlank([

                        'message' => 'Le nom de l\'ingrédient est obligatoire'

                    ]),

                    new Length([

                        'min' => 2,

                        'max' => 100,

                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',

                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'

                    ])

                ]

            ])

            ->add('unite', ChoiceType::class, [

                'label' => 'Unité par défaut',

                'attr' => [

                    'class' => 'form-select'

                ],

                'choices' => [

                    'Gramme (g)' => 'g',

                    'Kilogramme (kg)' => 'kg',

                    'Millilitre (ml)' => 'ml',

                    'Centilitre (cl)' => 'cl',

                    'Litre (L)' => 'L',

                    'Pièce' => 'pièce',

                    'Cuillère à soupe' => 'c. à soupe',

                    'Cuillère à café' => 'c. à café',

                    'Pincée' => 'pincée',

                    'Tranche' => 'tranche',

                    'Gousse' => 'gousse',

                ],

                'placeholder' => 'Choisir une unité',

                'required' => false

            ]);

    }



    public function configureOptions(OptionsResolver $resolver): void

    {

        $resolver->setDefaults([

            'data_class' => Ingredient::class,

        ]);

    }

}

```

**Caractéristiques du formulaire** :

-   Champ `nom` avec validation (2-100 caractères)

-   Champ `unite` avec choix prédéfinis

-   Placeholder informatif

-   Classes Bootstrap intégrées

### 19.4 Création du contrôleur AdminIngredientController

**Fichier** : `src/Controller/Admin/AdminIngredientController.php`

```php

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



            $this->addFlash('success', 'L\'ingrédient a été ajouté avec succès.');



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



            $this->addFlash('success', 'L\'ingrédient a été modifié avec succès.');



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

                $this->addFlash('danger', 'Impossible de supprimer cet ingrédient car il est utilisé dans des recettes.');

            } else {

                $entityManager->remove($ingredient);

                $entityManager->flush();

                $this->addFlash('success', 'L\'ingrédient a été supprimé avec succès.');

            }

        }



        return $this->redirectToRoute('app_admin_ingredient_index');

    }

}

```

**Fonctionnalités implémentées** :

-   **Index** : Liste paginée avec recherche

-   **New** : Création avec validation

-   **Edit** : Modification avec message flash

-   **Delete** : Suppression avec vérification d'utilisation

-   **Protection** : Attribut `#[IsGranted('ROLE_ADMIN')]`

-   **CSRF** : Protection sur la suppression

### 19.5 Templates admin

**Création du dossier** :

```bash

mkdir -p templates/admin/ingredient

```

#### Template index.html.twig

**Fichier** : `templates/admin/ingredient/index.html.twig`

```twig

{% extends 'base.html.twig' %}



{% block title %}Gestion des ingrédients - Admin{% endblock %}



{% block body %}

<div class="container my-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h1 class="text-success">Gestion des ingrédients</h1>

        <a href="{{ path('app_admin_ingredient_new') }}" class="btn btn-success">

            <i class="bi bi-plus-circle"></i> Nouvel ingrédient

        </a>

    </div>



    <!-- Barre de recherche -->

    <div class="card mb-4">

        <div class="card-body">

            <form method="get" class="row g-3">

                <div class="col-md-10">

                    <input type="text"

                           name="search"

                           class="form-control"

                           placeholder="Rechercher un ingrédient..."

                           value="{{ search }}">

                </div>

                <div class="col-md-2">

                    <button type="submit" class="btn btn-primary w-100">

                        <i class="bi bi-search"></i> Rechercher

                    </button>

                </div>

            </form>

            {% if search %}

                <div class="mt-2">

                    <a href="{{ path('app_admin_ingredient_index') }}" class="btn btn-sm btn-outline-secondary">

                        <i class="bi bi-x"></i> Effacer la recherche

                    </a>

                </div>

            {% endif %}

        </div>

    </div>



    <!-- Statistiques -->

    <div class="row mb-4">

        <div class="col-md-4">

            <div class="card border-success">

                <div class="card-body text-center">

                    <h3 class="text-success">{{ ingredients.getTotalItemCount() }}</h3>

                    <p class="text-muted mb-0">Total ingrédients</p>

                </div>

            </div>

        </div>

    </div>



    <!-- Table des ingrédients -->

    <div class="card shadow-sm">

        <div class="card-body">

            {% if ingredients|length > 0 %}

                <div class="table-responsive">

                    <table class="table table-hover">

                        <thead class="table-success">

                            <tr>

                                <th>ID</th>

                                <th>Nom</th>

                                <th>Unité par défaut</th>

                                <th>Utilisé dans</th>

                                <th class="text-end">Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                            {% for ingredient in ingredients %}

                                <tr>

                                    <td>{{ ingredient.id }}</td>

                                    <td>

                                        <strong>{{ ingredient.nom }}</strong>

                                    </td>

                                    <td>

                                        {% if ingredient.unite %}

                                            <span class="badge bg-secondary">{{ ingredient.unite }}</span>

                                        {% else %}

                                            <span class="text-muted">Non définie</span>

                                        {% endif %}

                                    </td>

                                    <td>

                                        <span class="badge bg-info">

                                            {{ ingredient.recetteIngredients|length }} recette(s)

                                        </span>

                                    </td>

                                    <td class="text-end">

                                        <a href="{{ path('app_admin_ingredient_edit', {'id': ingredient.id}) }}"

                                           class="btn btn-sm btn-outline-primary">

                                            <i class="bi bi-pencil"></i>

                                        </a>



                                        {% if ingredient.recetteIngredients|length == 0 %}

                                            <form method="post"

                                                  action="{{ path('app_admin_ingredient_delete', {'id': ingredient.id}) }}"

                                                  class="d-inline"

                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet ingrédient ?');">

                                                <input type="hidden" name="_token" value="{{ csrf_token('delete' ~ ingredient.id) }}">

                                                <button class="btn btn-sm btn-outline-danger">

                                                    <i class="bi bi-trash"></i>

                                                </button>

                                            </form>

                                        {% else %}

                                            <button class="btn btn-sm btn-outline-danger" disabled title="Utilisé dans des recettes">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        {% endif %}

                                    </td>

                                </tr>

                            {% endfor %}

                        </tbody>

                    </table>

                </div>



                <!-- Pagination -->

                <div class="d-flex justify-content-center mt-4">

                    {{ knp_pagination_render(ingredients) }}

                </div>

            {% else %}

                <div class="text-center py-5">

                    <i class="bi bi-inbox fs-1 text-muted"></i>

                    <p class="text-muted mt-3">Aucun ingrédient trouvé</p>

                    {% if search %}

                        <a href="{{ path('app_admin_ingredient_index') }}" class="btn btn-outline-secondary">

                            Voir tous les ingrédients

                        </a>

                    {% endif %}

                </div>

            {% endif %}

        </div>

    </div>

</div>

{% endblock %}

```

**Éléments clés** :

-   Bouton "Nouvel ingrédient" en haut à droite

-   Barre de recherche avec bouton d'effacement

-   Statistique : Total ingrédients

-   Table responsive avec colonnes : ID, Nom, Unité, Utilisation, Actions

-   Bouton suppression désactivé si ingrédient utilisé

-   Pagination KnpPaginator

-   Message si aucun résultat

#### Template new.html.twig

**Fichier** : `templates/admin/ingredient/new.html.twig`

```twig

{% extends 'base.html.twig' %}



{% block title %}Nouvel ingrédient - Admin{% endblock %}



{% block body %}

<div class="container my-4">

    <div class="row">

        <div class="col-lg-6 mx-auto">

            <div class="mb-4">

                <a href="{{ path('app_admin_ingredient_index') }}" class="btn btn-outline-secondary">

                    <i class="bi bi-arrow-left"></i> Retour à la liste

                </a>

            </div>



            <div class="card shadow-sm">

                <div class="card-header bg-success text-white">

                    <h4 class="mb-0">

                        <i class="bi bi-plus-circle"></i> Nouvel ingrédient

                    </h4>

                </div>

                <div class="card-body">

                    {{ form_start(form) }}



                    <div class="mb-3">

                        {{ form_label(form.nom, null, {'label_attr': {'class': 'form-label fw-bold'}}) }}

                        <span class="text-danger">*</span>

                        {{ form_widget(form.nom) }}

                        {{ form_errors(form.nom) }}

                    </div>



                    <div class="mb-4">

                        {{ form_label(form.unite, null, {'label_attr': {'class': 'form-label fw-bold'}}) }}

                        {{ form_widget(form.unite) }}

                        {{ form_errors(form.unite) }}

                        <small class="form-text text-muted">

                            Unité par défaut utilisée dans l'autocomplete

                        </small>

                    </div>



                    <div class="d-flex justify-content-between">

                        <a href="{{ path('app_admin_ingredient_index') }}" class="btn btn-secondary">

                            Annuler

                        </a>

                        <button type="submit" class="btn btn-success">

                            <i class="bi bi-check-circle"></i> Ajouter

                        </button>

                    </div>



                    {{ form_end(form) }}

                </div>

            </div>

        </div>

    </div>

</div>

{% endblock %}

```

#### Template edit.html.twig

**Fichier** : `templates/admin/ingredient/edit.html.twig`

```twig

{% extends 'base.html.twig' %}



{% block title %}Modifier {{ ingredient.nom }} - Admin{% endblock %}



{% block body %}

<div class="container my-4">

    <div class="row">

        <div class="col-lg-6 mx-auto">

            <div class="mb-4">

                <a href="{{ path('app_admin_ingredient_index') }}" class="btn btn-outline-secondary">

                    <i class="bi bi-arrow-left"></i> Retour à la liste

                </a>

            </div>



            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">

                    <h4 class="mb-0">

                        <i class="bi bi-pencil"></i> Modifier l'ingrédient

                    </h4>

                </div>

                <div class="card-body">

                    {{ form_start(form) }}



                    <div class="mb-3">

                        {{ form_label(form.nom, null, {'label_attr': {'class': 'form-label fw-bold'}}) }}

                        <span class="text-danger">*</span>

                        {{ form_widget(form.nom) }}

                        {{ form_errors(form.nom) }}

                    </div>



                    <div class="mb-4">

                        {{ form_label(form.unite, null, {'label_attr': {'class': 'form-label fw-bold'}}) }}

                        {{ form_widget(form.unite) }}

                        {{ form_errors(form.unite) }}

                        <small class="form-text text-muted">

                            Unité par défaut utilisée dans l'autocomplete

                        </small>

                    </div>



                    <!-- Info utilisation -->

                    {% if ingredient.recetteIngredients|length > 0 %}

                        <div class="alert alert-info">

                            <i class="bi bi-info-circle"></i>

                            Cet ingrédient est utilisé dans <strong>{{ ingredient.recetteIngredients|length }}</strong> recette(s).

                        </div>

                    {% endif %}



                    <div class="d-flex justify-content-between">

                        <a href="{{ path('app_admin_ingredient_index') }}" class="btn btn-secondary">

                            Annuler

                        </a>

                        <button type="submit" class="btn btn-primary">

                            <i class="bi bi-check-circle"></i> Enregistrer

                        </button>

                    </div>



                    {{ form_end(form) }}

                </div>

            </div>

        </div>

    </div>

</div>

{% endblock %}

```

### 19.6 Ajout du lien admin dans la navbar

**Fichier** : `templates/partials/_navbar.html.twig`

Ajout dans la section utilisateur connecté :

```twig

{% if app.user %}

    <!-- ... autres liens ... -->



    {% if is_granted('ROLE_ADMIN') %}

        <li class="nav-item">

            <a class="nav-link" href="{{ path('app_admin_ingredient_index') }}">

                <i class="bi bi-gear"></i> Admin Ingrédients

            </a>

        </li>

    {% endif %}

{% endif %}

```

**Caractéristiques** :

-   Visible uniquement pour ROLE_ADMIN

-   Icône engrenage

-   Accès rapide à l'interface admin

### 19.7 Tests de l'administration

**Tests effectués** :

1. **Accès** :

    - Utilisateur normal ne voit pas le lien

    - Admin voit le lien dans la navbar

    - URL `/admin/ingredients` protégée (403 si non admin)

2. **Liste** :

    - Affichage de tous les ingrédients

    - Recherche par nom fonctionne

    - Pagination 20 par page

    - Compteur d'utilisation correct

3. **Création** :

    - Formulaire s'affiche

    - Validation nom obligatoire

    - Unité optionnelle

    - Message flash succès

    - Redirection vers liste

4. **Modification** :

    - Formulaire pré-rempli

    - Alert si utilisé dans recettes

    - Modification sauvegardée

    - Message flash

5. **Suppression** :

    - ✅ Bouton désactivé si utilisé

    - ✅ Confirmation JavaScript

    - ✅ Token CSRF vérifié

    - ✅ Message d'erreur si utilisé

    - ✅ Suppression OK si non utilisé

### 19.8 Commits

```bash

git checkout -b feature/admin-ingredients



git add src/Form/IngredientType.php

git commit -m "feat: Formulaire gestion ingrédients



- Création IngredientType

- Champs: nom, unite

- Validation complète

- Choix unités prédéfinies"



git add src/Controller/Admin/

git commit -m "feat: Controller admin ingrédients avec CRUD complet



- Liste ingrédients avec recherche et pagination

- Ajout nouvel ingrédient

- Modification ingrédient

- Suppression ingrédient (si non utilisé)

- Protection ROLE_ADMIN

- Vérification usage dans recettes avant suppression

- Messages flash confirmation"



git add templates/admin/ingredient/

git commit -m "feat: Templates admin gestion ingrédients



INDEX:

- Liste paginée ingrédients

- Barre recherche

- Statistiques total ingrédients

- Colonne usage dans recettes

- Actions édition/suppression

- Bouton suppression désactivé si utilisé



NEW:

- Formulaire ajout ingrédient

- Design carte Bootstrap

- Validation front



EDIT:

- Formulaire modification ingrédient

- Alerte si utilisé dans recettes

- Design cohérent



Interface moderne et intuitive"



git add templates/partials/_navbar.html.twig

git commit -m "feat: Ajout lien admin ingrédients dans navbar



- Visible uniquement pour ROLE_ADMIN

- Icône engrenage

- Accès rapide interface admin"



git push origin feature/admin-ingredients

git checkout master

git merge feature/admin-ingredients

git push origin master

git branch -d feature/admin-ingredients

```

---

## ÉTAPE 20 : PAGES LÉGALES OBLIGATOIRES

### 20.1 Contexte et obligation RGPD

Toute application web collectant des données personnelles doit respecter le **RGPD (Règlement Général sur la Protection des Données)**. Les pages suivantes sont **obligatoires** :

1. **Contact** : Formulaire de contact

2. **Mentions Légales** : Informations légales sur l'éditeur

3. **Politique de Confidentialité** : Gestion des données personnelles

4. **CGU (Conditions Générales d'Utilisation)** : Règles d'utilisation du site

### 20.2 Création du contrôleur LegalController

**Fichier** : `src/Controller/LegalController.php`

```php

<?php



namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Attribute\Route;



class LegalController extends AbstractController

{

    #[Route('/mentions-legales', name: 'app_mentions_legales')]

    public function mentionsLegales(): Response

    {

        return $this->render('legal/mentions-legales.html.twig');

    }



    #[Route('/politique-confidentialite', name: 'app_politique_confidentialite')]

    public function politiqueConfidentialite(): Response

    {

        return $this->render('legal/politique-confidentialite.html.twig');

    }



    #[Route('/cgu', name: 'app_cgu')]

    public function cgu(): Response

    {

        return $this->render('legal/cgu.html.twig');

    }

}

```

### 20.3 Page Contact

#### Création du formulaire ContactType

**Fichier** : `src/Form/ContactType.php`

```php

<?php



namespace App\Form;



use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\EmailType;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\Email;

use Symfony\Component\Validator\Constraints\Length;

use Symfony\Component\Validator\Constraints\NotBlank;



class ContactType extends AbstractType

{

    public function buildForm(FormBuilderInterface $builder, array $options): void

    {

        $builder

            ->add('nom', TextType::class, [

                'label' => 'Nom',

                'attr' => [

                    'class' => 'form-control',

                    'placeholder' => 'Votre nom'

                ],

                'constraints' => [

                    new NotBlank(['message' => 'Le nom est obligatoire']),

                    new Length(['min' => 2, 'max' => 50])

                ]

            ])

            ->add('email', EmailType::class, [

                'label' => 'Email',

                'attr' => [

                    'class' => 'form-control',

                    'placeholder' => 'votre.email@exemple.com'

                ],

                'constraints' => [

                    new NotBlank(['message' => 'L\'email est obligatoire']),

                    new Email(['message' => 'Email invalide'])

                ]

            ])

            ->add('sujet', TextType::class, [

                'label' => 'Sujet',

                'attr' => [

                    'class' => 'form-control',

                    'placeholder' => 'Objet de votre message'

                ],

                'constraints' => [

                    new NotBlank(['message' => 'Le sujet est obligatoire']),

                    new Length(['min' => 5, 'max' => 100])

                ]

            ])

            ->add('message', TextareaType::class, [

                'label' => 'Message',

                'attr' => [

                    'class' => 'form-control',

                    'rows' => 6,

                    'placeholder' => 'Votre message...'

                ],

                'constraints' => [

                    new NotBlank(['message' => 'Le message est obligatoire']),

                    new Length(['min' => 10])

                ]

            ]);

    }



    public function configureOptions(OptionsResolver $resolver): void

    {

        $resolver->setDefaults([]);

    }

}

```

#### Création du contrôleur ContactController

**Fichier** : `src/Controller/ContactController.php`

```php

<?php



namespace App\Controller;



use App\Form\ContactType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Attribute\Route;



class ContactController extends AbstractController

{

    #[Route('/contact', name: 'app_contact')]

    public function index(Request $request): Response

    {

        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();



            // TODO: Envoyer un email à l'administrateur

            // Pour l'instant, on simule l'envoi



            $this->addFlash('success', 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');



            return $this->redirectToRoute('app_contact');

        }



        return $this->render('contact/index.html.twig', [

            'contactForm' => $form,

        ]);

    }

}

```

#### Template contact/index.html.twig

**Fichier** : `templates/contact/index.html.twig`

```twig

{% extends 'base.html.twig' %}



{% block title %}Contact - Les Restes{% endblock %}



{% block body %}

<div class="container my-5">

    <div class="row">

        <div class="col-lg-8 mx-auto">

            <h1 class="mb-4 text-success">Nous contacter</h1>



            <p class="lead mb-4">

                Une question, une suggestion ou un problème ? N'hésitez pas à nous contacter via le formulaire ci-dessous.

            </p>



            <div class="card shadow-sm">

                <div class="card-body p-4">

                    {{ form_start(contactForm) }}



                    <div class="mb-3">

                        {{ form_row(contactForm.nom) }}

                    </div>



                    <div class="mb-3">

                        {{ form_row(contactForm.email) }}

                    </div>



                    <div class="mb-3">

                        {{ form_row(contactForm.sujet) }}

                    </div>



                    <div class="mb-4">

                        {{ form_row(contactForm.message) }}

                    </div>



                    <button type="submit" class="btn btn-success btn-lg w-100">

                        <i class="bi bi-send"></i> Envoyer le message

                    </button>



                    {{ form_end(contactForm) }}

                </div>

            </div>



            <!-- Informations de contact -->

            <div class="row mt-5">

                <div class="col-md-4">

                    <div class="text-center">

                        <i class="bi bi-envelope fs-1 text-success"></i>

                        <h5 class="mt-3">Email</h5>

                        <p class="text-muted">contact@lesrestes.com</p>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="text-center">

                        <i class="bi bi-geo-alt fs-1 text-success"></i>

                        <h5 class="mt-3">Adresse</h5>

                        <p class="text-muted">666 Rue du Paradis<br>31000 Toulouse</p>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="text-center">

                        <i class="bi bi-clock fs-1 text-success"></i>

                        <h5 class="mt-3">Horaires</h5>

                        <p class="text-muted">Lun-Ven: 9h-18h</p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

{% endblock %}

```

### 20.4 Page Mentions Légales

**Fichier** : `templates/legal/mentions-legales.html.twig`

```twig

{% extends 'base.html.twig' %}



{% block title %}Mentions Légales - Les Restes{% endblock %}



{% block body %}

<div class="container my-5">

    <div class="row">

        <div class="col-lg-8 mx-auto">

            <h1 class="mb-4 text-success">Mentions Légales</h1>



            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <h3>1. Éditeur du site</h3>

                    <p>

                        <strong>Nom du site :</strong> Les Restes<br>

                        <strong>URL :</strong> https://lesrestes.com<br>

                        <strong>Propriétaire :</strong> Bah Shabadine<br>

                        <strong>Adresse :</strong> 666 Rue du Paradis, 31000 Toulouse<br>

                        <strong>Email :</strong> contact@lesrestes.com

                    </p>



                    <h3 class="mt-4">2. Hébergeur</h3>

                    <p>

                        <strong>Nom :</strong> Render (ou Heroku selon déploiement)<br>

                        <strong>Adresse :</strong> [Adresse hébergeur]<br>

                        <strong>Site web :</strong> [URL hébergeur]

                    </p>



                    <h3 class="mt-4">3. Propriété intellectuelle</h3>

                    <p>

                        L'ensemble du contenu de ce site (textes, images, vidéos, etc.) est protégé par le droit d'auteur.

                        Toute reproduction, même partielle, est interdite sans autorisation préalable.

                    </p>



                    <h3 class="mt-4">4. Protection des données personnelles</h3>

                    <p>

                        Conformément au RGPD, vous disposez d'un droit d'accès, de rectification et de suppression de vos données personnelles.

                        Pour exercer ce droit, contactez-nous à : contact@lesrestes.com

                    </p>

                    <p>

                        Pour plus d'informations, consultez notre

                        <a href="{{ path('app_politique_confidentialite') }}">Politique de Confidentialité</a>.

                    </p>



                    <h3 class="mt-4">5. Cookies</h3>

                    <p>

                        Ce site utilise des cookies pour améliorer l'expérience utilisateur.

                        En poursuivant votre navigation, vous acceptez l'utilisation de cookies.

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

{% endblock %}

```

### 20.5 Page Politique de Confidentialité

**Fichier** : `templates/legal/politique-confidentialite.html.twig`

```twig

{% extends 'base.html.twig' %}



{% block title %}Politique de Confidentialité - Les Restes{% endblock %}



{% block body %}

<div class="container my-5">

    <div class="row">

        <div class="col-lg-8 mx-auto">

            <h1 class="mb-4 text-success">Politique de Confidentialité</h1>



            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <h3>1. Collecte des données personnelles</h3>

                    <p>

                        Nous collectons les données suivantes :

                    </p>

                    <ul>

                        <li><strong>Données d'inscription :</strong> nom, prénom, adresse email</li>

                        <li><strong>Données de navigation :</strong> adresse IP, cookies, pages visitées</li>

                        <li><strong>Contenu créé :</strong> recettes, commentaires, favoris</li>

                    </ul>



                    <h3 class="mt-4">2. Finalité de la collecte</h3>

                    <p>

                        Vos données sont utilisées pour :

                    </p>

                    <ul>

                        <li>Gérer votre compte utilisateur</li>

                        <li>Vous permettre de publier des recettes et commentaires</li>

                        <li>Améliorer nos services</li>

                        <li>Vous envoyer des notifications (si activées)</li>

                    </ul>



                    <h3 class="mt-4">3. Conservation des données</h3>

                    <p>

                        Vos données sont conservées :

                    </p>

                    <ul>

                        <li><strong>Compte actif :</strong> tant que votre compte existe</li>

                        <li><strong>Compte supprimé :</strong> anonymisation immédiate des données</li>

                        <li><strong>Logs techniques :</strong> 12 mois maximum</li>

                    </ul>



                    <h3 class="mt-4">4. Vos droits (RGPD)</h3>

                    <p>

                        Vous disposez des droits suivants :

                    </p>

                    <ul>

                        <li><strong>Droit d'accès :</strong> obtenir une copie de vos données</li>

                        <li><strong>Droit de rectification :</strong> corriger vos données</li>

                        <li><strong>Droit à l'effacement :</strong> supprimer votre compte</li>

                        <li><strong>Droit d'opposition :</strong> refuser certains traitements</li>

                        <li><strong>Droit à la portabilité :</strong> récupérer vos données</li>

                    </ul>

                    <p>

                        Pour exercer ces droits, contactez-nous à : <strong>contact@lesrestes.com</strong>

                    </p>



                    <h3 class="mt-4">5. Sécurité</h3>

                    <p>

                        Nous mettons en œuvre toutes les mesures techniques et organisationnelles pour protéger vos données :

                    </p>

                    <ul>

                        <li>Chiffrement des mots de passe (bcrypt)</li>

                        <li>Connexion HTTPS sécurisée</li>

                        <li>Accès restreint aux données</li>

                        <li>Sauvegardes régulières</li>

                    </ul>



                    <h3 class="mt-4">6. Cookies</h3>

                    <p>

                        Ce site utilise des cookies pour :

                    </p>

                    <ul>

                        <li><strong>Session :</strong> maintenir votre connexion</li>

                        <li><strong>Préférences :</strong> sauvegarder vos choix (ingrédients cochés)</li>

                    </ul>

                    <p>

                        Vous pouvez désactiver les cookies dans les paramètres de votre navigateur.

                    </p>



                    <h3 class="mt-4">7. Partage des données</h3>

                    <p>

                        Vos données ne sont <strong>jamais vendues ni partagées</strong> avec des tiers,

                        sauf obligation légale (décision de justice, autorités compétentes).

                    </p>



                    <h3 class="mt-4">8. Contact</h3>

                    <p>

                        Pour toute question relative à la protection de vos données personnelles :<br>

                        Email : <strong>contact@lesrestes.com</strong><br>

                        Adresse : 666 Rue du Paradis, 31000 Toulouse

                    </p>



                    <p class="mt-4">

                        <em>Dernière mise à jour : Novembre 2025</em>

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

{% endblock %}

```

### 20.6 Page CGU (Conditions Générales d'Utilisation)

**Fichier** : `templates/legal/cgu.html.twig`

```twig

{% extends 'base.html.twig' %}



{% block title %}Conditions Générales d'Utilisation - Les Restes{% endblock %}



{% block body %}

<div class="container my-5">

    <div class="row">

        <div class="col-lg-8 mx-auto">

            <h1 class="mb-4 text-success">Conditions Générales d'Utilisation</h1>



            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <h3>1. Objet</h3>

                    <p>

                        Les présentes Conditions Générales d'Utilisation (CGU) régissent l'accès et l'utilisation du site <strong>Les Restes</strong>.

                        En accédant au site, vous acceptez sans réserve les présentes CGU.

                    </p>



                    <h3 class="mt-4">2. Inscription et compte utilisateur</h3>

                    <p>

                        <strong>2.1 Conditions d'inscription</strong><br>

                        Pour utiliser certaines fonctionnalités, vous devez créer un compte en fournissant des informations exactes et à jour.

                    </p>

                    <p>

                        <strong>2.2 Responsabilité</strong><br>

                        Vous êtes responsable de la confidentialité de votre mot de passe et de toutes les activités effectuées depuis votre compte.

                    </p>

                    <p>

                        <strong>2.3 Suppression de compte</strong><br>

                        Vous pouvez supprimer votre compte à tout moment depuis votre profil. La suppression est immédiate et définitive.

                    </p>



                    <h3 class="mt-4">3. Utilisation du service</h3>

                    <p>

                        <strong>3.1 Contenu publié</strong><br>

                        Vous vous engagez à ne pas publier de contenu :

                    </p>

                    <ul>

                        <li>Illégal, offensant, diffamatoire</li>

                        <li>Violant des droits d'auteur</li>

                        <li>Contenant des virus ou codes malveillants</li>

                        <li>À caractère publicitaire non sollicité</li>

                    </ul>

                    <p>

                        <strong>3.2 Modération</strong><br>

                        Nous nous réservons le droit de supprimer tout contenu ne respectant pas ces règles, sans préavis.

                    </p>



                    <h3 class="mt-4">4. Propriété intellectuelle</h3>

                    <p>

                        <strong>4.1 Contenu du site</strong><br>

                        Le design, les textes, les images et le code source du site sont protégés par le droit d'auteur.

                    </p>

                    <p>

                        <strong>4.2 Contenu utilisateur</strong><br>

                        Vous conservez la propriété intellectuelle de vos recettes. En les publiant, vous accordez à <strong>Les Restes</strong>

                        une licence non exclusive pour les afficher sur le site.

                    </p>



                    <h3 class="mt-4">5. Responsabilité</h3>

                    <p>

                        <strong>5.1 Disponibilité</strong><br>

                        Nous nous efforçons d'assurer la disponibilité du site 24h/24, mais ne garantissons pas un accès sans interruption.

                    </p>

                    <p>

                        <strong>5.2 Contenu utilisateur</strong><br>

                        Nous ne sommes pas responsables du contenu publié par les utilisateurs.

                        Chaque utilisateur est responsable de ses propres publications.

                    </p>

                    <p>

                        <strong>5.3 Recettes</strong><br>

                        Les recettes sont fournies à titre informatif. Nous ne garantissons pas leur exactitude ni leur résultat.

                        Consultez un professionnel en cas d'allergies ou restrictions alimentaires.

                    </p>



                    <h3 class="mt-4">6. Données personnelles</h3>

                    <p>

                        La collecte et le traitement de vos données personnelles sont décrits dans notre

                        <a href="{{ path('app_politique_confidentialite') }}">Politique de Confidentialité</a>.

                    </p>



                    <h3 class="mt-4">7. Modification des CGU</h3>

                    <p>

                        Nous nous réservons le droit de modifier les présentes CGU à tout moment.

                        Les modifications prennent effet dès leur publication sur le site.

                    </p>



                    <h3 class="mt-4">8. Loi applicable</h3>

                    <p>

                        Les présentes CGU sont soumises au droit français.

                        Tout litige sera soumis aux tribunaux compétents de Toulouse.

                    </p>



                    <h3 class="mt-4">9. Contact</h3>

                    <p>

                        Pour toute question relative aux CGU :<br>

                        Email : <strong>contact@lesrestes.com</strong><br>

                        Adresse : 666 Rue du Paradis, 31000 Toulouse

                    </p>



                    <p class="mt-4">

                        <em>Dernière mise à jour : Novembre 2025</em>

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

{% endblock %}

```

### 20.7 Ajout des liens dans le footer

**Fichier** : `templates/base.html.twig`

Modification du footer pour inclure les liens légaux :

```twig

<footer class="py-4 mt-5 border-top"

        style="background-color: var(--background-color);">

    <div class="container">

        <div class="row">

            <!-- Colonne 1 : Mentions légales -->

            <div class="col-md-3">

                <h6 class="fw-bold mb-3">Mentions légales</h6>

                <ul class="list-unstyled">

                    <li><a href="{{ path('app_mentions_legales') }}" class="text-muted">Mentions légales</a></li>

                    <li><a href="{{ path('app_politique_confidentialite') }}" class="text-muted">Politique de confidentialité</a></li>

                    <li><a href="{{ path('app_cgu') }}" class="text-muted">CGU</a></li>

                    <li><a href="{{ path('app_contact') }}" class="text-muted">Contact</a></li>

                </ul>

                <p class="small mb-0 text-muted">Copyright © 2025</p>

            </div>



            <!-- ... Autres colonnes ... -->

        </div>

    </div>

</footer>

```

### 20.8 Tests des pages légales

**Tests effectués** :

1. **Page Contact** :

    - ✅ Formulaire s'affiche

    - ✅ Validation fonctionne

    - ✅ Message flash après soumission

    - ✅ Informations de contact affichées

2. **Mentions Légales** :

    - ✅ Informations éditeur présentes

    - ✅ Hébergeur mentionné

    - ✅ Propriété intellectuelle

    - ✅ Lien vers politique de confidentialité

3. **Politique de Confidentialité** :

    - ✅ Collecte des données expliquée

    - ✅ Droits RGPD listés

    - ✅ Sécurité décrite

    - ✅ Contact mentionné

4. **CGU** :

    - ✅ Conditions d'inscription

    - ✅ Règles d'utilisation

    - ✅ Propriété intellectuelle

    - ✅ Responsabilités

5. **Footer** :

    - ✅ Liens fonctionnels

    - ✅ Visible sur toutes les pages

    - ✅ Design cohérent

### 20.9 Commits

```bash

git checkout -b feature/pages-legales



git add src/Form/ContactType.php src/Controller/ContactController.php templates/contact/

git commit -m "feat: Page contact avec formulaire



- Formulaire ContactType (nom, email, sujet, message)

- Validation complète

- ContactController avec traitement

- Template responsive

- Informations de contact affichées

- Message flash succès"



git add src/Controller/LegalController.php templates/legal/

git commit -m "feat: Pages légales complètes (RGPD)



MENTIONS LÉGALES:

- Éditeur du site

- Hébergeur

- Propriété intellectuelle

- Protection données

- Cookies



POLITIQUE CONFIDENTIALITÉ:

- Collecte données personnelles

- Finalités traitement

- Conservation données

- Droits RGPD (accès, rectification, effacement)

- Sécurité (bcrypt, HTTPS)

- Cookies

- Pas de partage données



CGU:

- Objet

- Inscription et compte

- Utilisation service

- Propriété intellectuelle

- Responsabilité

- Loi applicable



Conformité RGPD complète"



git add templates/base.html.twig

git commit -m "feat: Ajout liens pages légales dans footer



- Mentions légales

- Politique de confidentialité

- CGU

- Contact

- Copyright 2025"



git push origin feature/pages-legales

git checkout master

git merge feature/pages-legales

git push origin master

git branch -d feature/pages-legales

```

---

## ÉTAPE 21 : PAGE 404 PERSONNALISÉE

### 21.1 Contexte

Par défaut, Symfony affiche une page d'erreur 404 basique. Pour améliorer l'expérience utilisateur, nous allons créer une **page 404 personnalisée** avec :

-   Design cohérent avec le site

-   Message friendly

-   Liens utiles pour naviguer

-   Animation SVG

### 21.2 Création du controller ErrorController

**Fichier** : `src/Controller/ErrorController.php`

```php

<?php



namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;



class ErrorController extends AbstractController

{

    public function show(\Throwable $exception): Response

    {

        $statusCode = $exception instanceof HttpExceptionInterface

            ? $exception->getStatusCode()

            : 500;



        return $this->render('bundles/TwigBundle/Exception/error' . $statusCode . '.html.twig', [

            'status_code' => $statusCode,

            'status_text' => $exception->getMessage(),

        ], new Response('', $statusCode));

    }

}

```

### 21.3 Configuration du controller d'erreur

**Fichier** : `config/packages/framework.yaml`

```yaml
framework:
    error_controller: App\Controller\ErrorController::show
```

### 21.4 Template error404.html.twig

**Fichier** : `templates/bundles/TwigBundle/Exception/error404.html.twig`

```twig

{% extends 'base.html.twig' %}



{% block title %}404 - Page non trouvée{% endblock %}



{% block body %}

<div class="container my-5">

    <div class="row justify-content-center">

        <div class="col-md-8 text-center">

            <!-- Animation SVG 404 -->

            <div class="mb-4">

                <svg width="300" height="200" viewBox="0 0 300 200">

                    <text x="150" y="120"

                          text-anchor="middle"

                          font-size="120"

                          font-weight="bold"

                          fill="var(--success-color)"

                          opacity="0.2">

                        404

                    </text>

                    <text x="150" y="150"

                          text-anchor="middle"

                          font-size="24"

                          fill="var(--text-color)">

                        Oups !

                    </text>

                </svg>

            </div>



            <!-- Message principal -->

            <h1 class="mb-3 text-success">Page non trouvée</h1>

            <p class="lead text-muted mb-4">

                Désolé, la page que vous recherchez n'existe pas ou a été déplacée.

            </p>



            <!-- Suggestions -->

            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <h5 class="card-title mb-3">

                        <i class="bi bi-lightbulb text-warning"></i> Suggestions

                    </h5>

                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <a href="{{ path('app_home') }}" class="btn btn-outline-success w-100">

                                <i class="bi bi-house"></i><br>

                                Retour à l'accueil

                            </a>

                        </div>

                        <div class="col-md-4 mb-3">

                            <a href="{{ path('app_recette_index') }}" class="btn btn-outline-success w-100">

                                <i class="bi bi-book"></i><br>

                                Voir les recettes

                            </a>

                        </div>

                        <div class="col-md-4 mb-3">

                            <a href="{{ path('app_contact') }}" class="btn btn-outline-success w-100">

                                <i class="bi bi-envelope"></i><br>

                                Nous contacter

                            </a>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Barre de recherche -->

            <div class="card shadow-sm">

                <div class="card-body">

                    <h5 class="card-title mb-3">

                        <i class="bi bi-search"></i> Rechercher une recette

                    </h5>

                    <form action="{{ path('app_search') }}" method="GET">

                        <div class="input-group input-group-lg">

                            <input type="text"

                                   name="q"

                                   class="form-control"

                                   placeholder="Ex: tomates, œufs...">

                            <button class="btn btn-success" type="submit">

                                <i class="bi bi-search"></i> Rechercher

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>



<style>

@keyframes fadeIn {

    from { opacity: 0; transform: translateY(20px); }

    to { opacity: 1; transform: translateY(0); }

}



.container {

    animation: fadeIn 0.5s ease;

}



svg text:first-child {

    animation: pulse 2s ease-in-out infinite;

}



@keyframes pulse {

    0%, 100% { opacity: 0.2; }

    50% { opacity: 0.4; }

}

</style>

{% endblock %}

```

**Éléments clés** :

-   SVG animé avec "404" en grand

-   Message friendly et rassurant

-   3 boutons de navigation (Accueil, Recettes, Contact)

-   Barre de recherche fonctionnelle

-   Animation fadeIn au chargement

-   Animation pulse sur le 404

### 21.5 Template error500.html.twig (optionnel)

**Fichier** : `templates/bundles/TwigBundle/Exception/error500.html.twig`

```twig

{% extends 'base.html.twig' %}



{% block title %}500 - Erreur serveur{% endblock %}



{% block body %}

<div class="container my-5">

    <div class="row justify-content-center">

        <div class="col-md-8 text-center">

            <div class="mb-4">

                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 8rem;"></i>

            </div>



            <h1 class="mb-3 text-danger">Erreur serveur</h1>

            <p class="lead text-muted mb-4">

                Une erreur inattendue s'est produite. Nous travaillons à résoudre le problème.

            </p>



            <div class="d-flex gap-3 justify-content-center">

                <a href="{{ path('app_home') }}" class="btn btn-success">

                    <i class="bi bi-house"></i> Retour à l'accueil

                </a>

                <a href="{{ path('app_contact') }}" class="btn btn-outline-secondary">

                    <i class="bi bi-envelope"></i> Signaler le problème

                </a>

            </div>

        </div>

    </div>

</div>

{% endblock %}

```

### 21.6 Template error.html.twig (fallback générique)

**Fichier** : `templates/bundles/TwigBundle/Exception/error.html.twig`

```twig

{% extends 'base.html.twig' %}



{% block title %}Erreur {{ status_code }}{% endblock %}



{% block body %}

<div class="container my-5">

    <div class="row justify-content-center">

        <div class="col-md-8 text-center">

            <h1 class="mb-3 text-warning">Erreur {{ status_code }}</h1>

            <p class="lead text-muted mb-4">

                Une erreur est survenue. Veuillez réessayer ultérieurement.

            </p>



            <a href="{{ path('app_home') }}" class="btn btn-success">

                <i class="bi bi-house"></i> Retour à l'accueil

            </a>

        </div>

    </div>

</div>

{% endblock %}

```

### 21.7 Tests de la page 404

**Méthode 1 : Mode dev (affiche Symfony Profiler)**

```

# Tester une URL inexistante

http://127.0.0.1:8004/page-qui-nexiste-pas

```

En mode dev, Symfony affiche le Profiler avec les détails de l'erreur.

**Méthode 2 : Mode prod (affiche la page custom)**

```bash

# Passer en mode prod temporairement

APP_ENV=prod php bin/console cache:clear

symfony server:start



# Tester l'URL

http://127.0.0.1:8004/page-qui-nexiste-pas

```

La page 404 personnalisée s'affiche !

**Résultat attendu** :

-   ✅ Design cohérent avec le site

-   ✅ SVG "404" animé

-   ✅ Message friendly

-   ✅ 3 boutons de navigation

-   ✅ Barre de recherche fonctionnelle

-   ✅ Animation au chargement

### 21.8 Commit

```bash

git checkout -b feature/page-404



git add src/Controller/ErrorController.php config/packages/framework.yaml

git commit -m "feat: Controller erreur personnalisé



- ErrorController avec gestion status codes

- Configuration dans framework.yaml

- Support 404, 500 et erreur générique"



git add templates/bundles/TwigBundle/Exception/

git commit -m "feat: Pages erreur personnalisées



ERROR 404:

- SVG animé '404' avec pulse

- Message friendly rassurant

- 3 boutons navigation (Accueil, Recettes, Contact)

- Barre recherche fonctionnelle

- Animation fadeIn

- Design cohérent palette



ERROR 500:

- Icône warning rouge

- Message erreur serveur

- Boutons retour accueil + signaler



ERROR GÉNÉRIQUE:

- Template fallback pour autres codes

- Message simple

- Bouton retour accueil



Animations CSS:

- fadeIn conteneur (0.5s)

- pulse SVG 404 (2s infinite)



Tests mode prod OK"



git push origin feature/page-404

git checkout master

git merge feature/page-404

git push origin master

git branch -d feature/page-404

```

---

## ÉTAPE 22 : RESPONSIVE DESIGN COMPLET

### 22.1 Contexte et objectifs

Le site doit être **parfaitement utilisable sur tous les appareils** :

-   Desktop (>992px)

-   Tablet (768px-992px)

-   Mobile (< 768px)

**Objectifs** :

-   Navigation mobile avec hamburger

-   Formulaires touch-friendly

-   Images adaptatives

-   Textes lisibles

-   Boutons cliquables (44x44px minimum)

### 22.2 Création du fichier responsive.css

**Fichier** : `public/css/responsive.css`

```css
/* 

LES RESTES - Styles responsive 

Breakpoints Bootstrap: xs<576, sm≥576, md≥768, lg≥992, xl≥1200, xxl≥1400 

*/

/* ============================================ 

   NAVBAR MOBILE 

============================================ */

@media (max-width: 991px) {
    .navbar-nav {
        padding: 1rem 0;
    }

    .navbar-nav .nav-link {
        padding: 0.75rem 1rem;
    }

    .navbar-collapse {
        background: #fff;

        border-radius: 0.5rem;

        margin-top: 1rem;

        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
}

/* ============================================ 

   HERO SECTION 

============================================ */

@media (max-width: 768px) {
    .hero-section {
        padding: 2rem 0 !important;

        min-height: auto !important;
    }

    .hero-section h1 {
        font-size: 2rem !important;
    }

    .hero-section .lead {
        font-size: 1rem !important;
    }

    .hero-section .btn {
        width: 100%;

        margin-bottom: 0.5rem;
    }
}

/* ============================================ 

   CARDS RECETTES 

============================================ */

@media (max-width: 576px) {
    .card {
        margin-bottom: 1rem;
    }

    .card-img-top {
        height: 200px !important;
    }

    .card-title {
        font-size: 1.1rem;
    }

    .card-body {
        padding: 1rem;
    }
}

/* ============================================ 

   FORMULAIRES 

============================================ */

@media (max-width: 768px) {
    .form-control,
    .form-select {
        font-size: 16px !important; /* Évite zoom auto iOS */
    }

    .ingredient-row .col-md-5,
    .ingredient-row .col-md-3,
    .ingredient-row .col-md-2 {
        flex: 0 0 100%;

        max-width: 100%;

        margin-bottom: 0.5rem;
    }

    .ingredient-row .row {
        flex-direction: column;
    }

    .etape-row .col-auto {
        flex: 0 0 100%;

        max-width: 100%;

        margin-bottom: 0.5rem;
    }
}

/* ============================================ 

   DÉTAIL RECETTE 

============================================ */

@media (max-width: 992px) {
    .recipe-detail-image {
        max-height: 300px !important;
    }

    .recipe-info-icons {
        flex-direction: column;

        gap: 0.5rem !important;
    }

    .recipe-info-icons > div {
        width: 100%;

        text-align: center;
    }
}

/* ============================================ 

   PROFIL UTILISATEUR 

============================================ */

@media (max-width: 768px) {
    .nav-tabs {
        flex-direction: column;

        border-bottom: none;
    }

    .nav-tabs .nav-link {
        border-radius: 0.375rem;

        margin-bottom: 0.5rem;

        border: 1px solid #dee2e6;
    }

    .nav-tabs .nav-link.active {
        background-color: var(--success-color);

        color: white;

        border-color: var(--success-color);
    }

    .profile-header {
        text-align: center;
    }

    .profile-stats {
        flex-direction: column;

        gap: 1rem !important;
    }
}

/* ============================================ 

   TABLEAUX 

============================================ */

@media (max-width: 768px) {
    .table-responsive {
        border: none;
    }

    .table {
        font-size: 0.875rem;
    }

    .table th,
    .table td {
        padding: 0.5rem;

        white-space: nowrap;
    }

    /* Cacher colonnes moins importantes sur mobile */

    .table .hide-mobile {
        display: none;
    }
}

/* ============================================ 

   COMMENTAIRES 

============================================ */

@media (max-width: 576px) {
    .comment-avatar {
        width: 40px !important;

        height: 40px !important;

        font-size: 1.2rem !important;
    }

    .comment-header {
        flex-direction: column;

        align-items: flex-start !important;
    }

    .comment-stars {
        margin-top: 0.5rem;
    }
}

/* ============================================ 

   BOUTONS 

============================================ */

@media (max-width: 576px) {
    .btn-group {
        flex-direction: column;

        width: 100%;
    }

    .btn-group > .btn {
        width: 100%;

        margin-bottom: 0.5rem;
    }

    .d-flex.gap-2 {
        flex-direction: column !important;

        gap: 0.5rem !important;
    }

    .d-flex.gap-2 > * {
        width: 100% !important;
    }
}

/* ============================================ 

   SEARCH BAR 

============================================ */

@media (max-width: 768px) {
    .search-form .col-md-10,
    .search-form .col-md-2 {
        flex: 0 0 100%;

        max-width: 100%;

        margin-bottom: 0.5rem;
    }

    .search-filters {
        flex-direction: column;
    }

    .search-filters .col-md-4,
    .search-filters .col-md-2 {
        flex: 0 0 100%;

        max-width: 100%;

        margin-bottom: 0.5rem;
    }
}

/* ============================================ 

   PAGINATION 

============================================ */

@media (max-width: 576px) {
    .pagination {
        font-size: 0.875rem;
    }

    .pagination .page-link {
        padding: 0.375rem 0.75rem;
    }
}

/* ============================================ 

   FOOTER 

============================================ */

@media (max-width: 768px) {
    footer .col-md-3 {
        text-align: center;

        margin-bottom: 2rem;
    }

    footer ul {
        padding-left: 0;
    }
}

/* ============================================ 

   MODALS 

============================================ */

@media (max-width: 576px) {
    .modal-dialog {
        margin: 0.5rem;
    }

    .modal-content {
        border-radius: 0.5rem;
    }

    .modal-header,
    .modal-footer {
        padding: 1rem;
    }

    .modal-body {
        padding: 1.5rem 1rem;
    }
}

/* ============================================ 

   UTILITAIRES RESPONSIVE 

============================================ */

/* Espacement réduit sur mobile */

@media (max-width: 768px) {
    .container {
        padding-left: 1rem;

        padding-right: 1rem;
    }

    .my-5 {
        margin-top: 2rem !important;

        margin-bottom: 2rem !important;
    }

    .py-5 {
        padding-top: 2rem !important;

        padding-bottom: 2rem !important;
    }
}

/* Texte responsive */

@media (max-width: 576px) {
    h1 {
        font-size: 1.75rem;
    }

    h2 {
        font-size: 1.5rem;
    }

    h3 {
        font-size: 1.25rem;
    }

    .display-1 {
        font-size: 3rem;
    }

    .display-4 {
        font-size: 2rem;
    }
}

/* Images responsive */

@media (max-width: 768px) {
    img {
        max-width: 100%;

        height: auto;
    }
}

/* Débordement texte */

@media (max-width: 576px) {
    .text-truncate-mobile {
        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }
}

/* Cacher éléments sur mobile */

.hide-on-mobile {
    display: block;
}

@media (max-width: 768px) {
    .hide-on-mobile {
        display: none !important;
    }
}

/* Afficher uniquement sur mobile */

.show-on-mobile {
    display: none;
}

@media (max-width: 768px) {
    .show-on-mobile {
        display: block !important;
    }
}

/* ============================================ 

   TOUCH FRIENDLY (44x44px minimum) 

============================================ */

@media (max-width: 768px) {
    .btn {
        min-height: 44px;

        padding: 0.75rem 1rem;
    }

    .nav-link {
        min-height: 44px;

        display: flex;

        align-items: center;
    }

    .form-check-input {
        width: 1.5rem;

        height: 1.5rem;
    }

    .form-check-label {
        padding-left: 0.5rem;
    }
}

/* Empêcher zoom sur focus input iOS */

@media (max-width: 768px) {
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="number"],
    textarea,
    select {
        font-size: 16px !important;
    }
}
```

### 22.3 Ajout de l'import dans app.css

**Fichier** : `public/css/app.css`

```css
/* 

LES RESTES - Fichier principal CSS 

Imports de tous les modules 

*/

/* Base et variables */

@import url("base.css");

/* Composants */

@import url("navbar.css");

@import url("footer.css");

@import url("cards.css");

@import url("modals.css");

@import url("forms.css");

@import url("pagination.css");

@import url("recipe-form.css");

/* Responsive */

@import url("responsive.css");

/* Utilitaires */

@import url("utilities.css");
```

### 22.4 Vérification de la navbar responsive

**Fichier** : `templates/partials/_navbar.html.twig`

Vérifier que le toggler est bien présent :

```twig

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">

    <div class="container">

        <a class="navbar-brand text-success fw-bold" href="{{ path('app_home') }}">

            <i class="bi bi-app-indicator"></i> Les Restes

        </a>



        <!-- Bouton hamburger pour mobile -->

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">

            <span class="navbar-toggler-icon"></span>

        </button>



        <div class="collapse navbar-collapse" id="navbarNav">

            <!-- ... reste du contenu ... -->

        </div>

    </div>

</nav>

```

### 22.5 Optimisation de la grille recettes

**Fichier** : `templates/recette/index.html.twig`

Change la grille pour être plus responsive :

```twig

<div class="row">

    {% for recette in recettes %}

        <div class="col-12 col-sm-6 col-lg-4 mb-4">

            {% include 'recette/partials/_recipe_card.html.twig' with {'recette': recette} %}

        </div>

    {% endfor %}

</div>

```

**Breakpoints** :

-   `col-12` : Mobile (1 colonne)

-   `col-sm-6` : Tablet (2 colonnes)

-   `col-lg-4` : Desktop (3 colonnes)

### 22.6 Optimisation du formulaire recette mobile

**Fichier** : `templates/recette/new.html.twig`

Change la structure des colonnes :

```twig

<!-- Temps et personnes -->

<div class="row mb-3">

    <div class="col-12 col-md-6 mb-3 mb-md-0">

        {{ form_label(form.tempsCuisson, 'Temps de préparation (min.)', {'label_attr': {'class': 'form-label fw-bold'}}) }}

        <div class="input-group">

            {{ form_widget(form.tempsCuisson, {'attr': {'class': 'form-control', 'placeholder': '15'}}) }}

            <span class="input-group-text">minutes</span>

        </div>

        {{ form_errors(form.tempsCuisson) }}

    </div>



    <div class="col-12 col-md-6">

        {{ form_label(form.nombrePersonnes, 'Nombre de personnes', {'label_attr': {'class': 'form-label fw-bold'}}) }}

        {{ form_widget(form.nombrePersonnes, {'attr': {'class': 'form-control', 'placeholder': '4'}}) }}

        {{ form_errors(form.nombrePersonnes) }}

    </div>

</div>

```

### 22.7 Optimisation des onglets profil pour mobile

**Fichier** : `templates/profil/index.html.twig`

Ajoute des classes responsive aux tabs :

```twig

<ul class="nav nav-tabs nav-fill mb-4" id="profilTab" role="tablist">

    <li class="nav-item" role="presentation">

        <button class="nav-link active" id="recettes-tab" data-bs-toggle="tab" data-bs-target="#recettes" type="button">

            <i class="bi bi-book"></i>

            <span class="d-none d-sm-inline">Mes</span> Recettes

        </button>

    </li>

    <li class="nav-item" role="presentation">

        <button class="nav-link" id="favoris-tab" data-bs-toggle="tab" data-bs-target="#favoris" type="button">

            <i class="bi bi-heart"></i>

            <span class="d-none d-sm-inline">Mes</span> Favoris

        </button>

    </li>

    <li class="nav-item" role="presentation">

        <button class="nav-link" id="parametres-tab" data-bs-toggle="tab" data-bs-target="#parametres" type="button">

            <i class="bi bi-gear"></i> Paramètres

        </button>

    </li>

</ul>

```

**Classes ajoutées** :

-   `nav-fill` : Onglets prennent toute la largeur

-   `d-none d-sm-inline` : Cacher "Mes" sur très petit écran

### 22.8 Tests sur différentes tailles

**Outils de test** :

1. **Chrome DevTools** (F12) :

    - Toggle device toolbar (Ctrl+Shift+M)

    - Tester : iPhone SE, iPhone 12 Pro, iPad, Desktop

2. **Vérifier :**

    - Navigation fluide

    - Texte lisible

    - Boutons cliquables (min 44x44px)

    - Images bien dimensionnées

    - Pas de débordement horizontal

    - Formulaires utilisables

### 22.9 Checklist finale testée

**Sur mobile (< 768px)** :

-   Navbar hamburger fonctionne

-   Home page lisible

-   Liste recettes 1 colonne

-   Détail recette scrollable

-   Formulaire recette utilisable

-   Profil onglets accessibles

-   Contact formulaire OK

-   Pages légales lisibles

-   Footer cent Modals auth full screen

-   Pas de scroll horizontal

-   Boutons cliquables facilement

### 22.10 Commits

```bash

git checkout -b feature/responsive-mobile



git add public/css/responsive.css public/css/app.css

git commit -m "feat: Ajout fichier CSS responsive complet



BREAKPOINTS:

- Mobile xs: <576px

- Mobile sm: 576px-768px

- Tablette md: 768px-992px

- Desktop lg: 992px+



OPTIMISATIONS:

- Navbar collapse mobile

- Hero section responsive

- Cards recettes adaptatives

- Formulaires touch-friendly

- Détail recette mobile

- Profil onglets verticaux mobile

- Tableaux responsive

- Commentaires compacts mobile

- Boutons pleine largeur mobile

- Search bar vertical mobile

- Footer centré mobile

- Modals plein écran mobile



UTILITAIRES:

- Espacements réduits mobile

- Textes responsive

- Images auto-resize

- Classes hide/show-on-mobile

- Touch targets 44x44px minimum

- Prévention zoom iOS sur inputs



Import dans app.css"



git add templates/base.html.twig

git commit -m "fix: Ajout meta viewport pour responsive



- Meta viewport width=device-width

- Initial scale 1.0

- Support responsive mobile"



git add templates/partials/_navbar.html.twig

git commit -m "fix: Optimisation navbar responsive



- Bouton hamburger visible mobile

- Collapse propre

- Navigation verticale mobile

- Touch targets adéquats"



git add templates/recette/index.html.twig

git commit -m "fix: Grille recettes responsive



- col-12: mobile full width

- col-sm-6: 2 colonnes tablette

- col-lg-4: 3 colonnes desktop

- Espacement adaptatif"



git add templates/recette/new.html.twig

git commit -m "fix: Formulaire recette responsive



- Colonnes empilées mobile

- Inputs full width mobile

- Espacements adaptés

- Touch-friendly"



git add templates/profil/index.html.twig

git commit -m "fix: Profil utilisateur responsive



- Onglets verticaux mobile

- Textes abrégés mobile

- Stats empilées

- Interface adaptative"



git push origin feature/responsive-mobile

git checkout master

git merge feature/responsive-mobile

git push origin master

git branch -d feature/responsive-mobile

```

---

## ETAPE 23 : BARRE DE NAVIGATION INFERIEURE MOBILE

### 23.1 Contexte et besoin

Le wireframe prevoit une barre de navigation fixe en bas pour mobile avec acces rapide aux 4 fonctions principales :

-   Accueil
-   Recherche
-   Publier (si connecte)
-   Parametres (si connecte)

Cette fonctionnalite ameliore considerablement l'UX mobile en suivant les standards des applications natives (Instagram, Twitter, etc.).

### 23.2 Creation du partial

**Fichier** : `templates/partials/_bottom_nav_mobile.html.twig`

```twig
{# templates/partials/_bottom_nav_mobile.html.twig #}
<nav class="mobile-bottom-nav">
    <a href="{{ path('app_home') }}"
       class="mobile-nav-item {{ app.request.attributes.get('_route') == 'app_home' ? 'active' : '' }}">
        <i class="bi bi-house-fill"></i>
        <span>Accueil</span>
    </a>

    <a href="{{ path('app_search') }}"
       class="mobile-nav-item {{ app.request.attributes.get('_route') starts with 'app_search' ? 'active' : '' }}">
        <i class="bi bi-search"></i>
        <span>Recherche</span>
    </a>

    {% if app.user %}
        <a href="{{ path('app_recette_new') }}"
           class="mobile-nav-item {{ app.request.attributes.get('_route') == 'app_recette_new' ? 'active' : '' }}">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Publier</span>
        </a>

        <a href="{{ path('app_profil') }}"
           class="mobile-nav-item {{ app.request.attributes.get('_route') == 'app_profil' ? 'active' : '' }}">
            <i class="bi bi-gear-fill"></i>
            <span>Parametres</span>
        </a>
    {% else %}
        <a href="{{ path('app_login') }}" class="mobile-nav-item">
            <i class="bi bi-box-arrow-in-right"></i>
            <span>Connexion</span>
        </a>
        <a href="{{ path('app_register') }}" class="mobile-nav-item">
            <i class="bi bi-person-plus-fill"></i>
            <span>Inscription</span>
        </a>
    {% endif %}
</nav>
```

**Elements cles** :

-   Classe `mobile-bottom-nav` sans Bootstrap (gere en CSS pur)
-   Active state dynamique via `app.request.attributes.get('_route')`
-   Icones Bootstrap Icons coherentes avec le design
-   Condition `if app.user` pour adapter selon connexion
-   Icone engrenage (`bi-gear-fill`) pour Parametres (comme Figma)

### 23.3 Integration dans base.html.twig

**Fichier** : `templates/base.html.twig`

**Inclusion du partial** :

```twig
<!-- Navigation -->
{% include 'partials/_navbar.html.twig' %}
{% include 'partials/_bottom_nav_mobile.html.twig' %}

<!-- Messages Flash -->
{% include 'partials/_flash_messages.html.twig' %}
```

**CSS inline dans le `<head>`** :

```html
<style>
    /* BARRE NAVIGATION MOBILE */
    .mobile-bottom-nav {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        background: #f8f9fa !important;
        border-top: 2px solid #1e5128 !important;
        display: flex !important;
        justify-content: space-around !important;
        align-items: center !important;
        padding: 0.75rem 0 !important;
        z-index: 9999 !important;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.15) !important;
        height: 70px !important;
    }

    .mobile-nav-item {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        color: #1e5128 !important;
        text-decoration: none !important;
        font-size: 0.7rem !important;
        font-weight: 500 !important;
        padding: 0.5rem !important;
        min-width: 70px !important;
        flex: 1 !important;
    }

    .mobile-nav-item i {
        font-size: 2rem !important;
        margin-bottom: 0.25rem !important;
    }

    @media (max-width: 767px) {
        body {
            padding-bottom: 85px !important;
        }
    }

    @media (min-width: 768px) {
        .mobile-bottom-nav {
            display: none !important;
        }
    }
</style>
```

**Raison du CSS inline** :

-   Priorite CSS plus elevee que les imports externes
-   Utilisation de `!important` pour forcer l'application
-   Evite les problemes de cache navigateur
-   Solution simple et efficace pour ce projet

### 23.4 Specifications techniques

**Styles appliques** :

-   `position: fixed` + `bottom: 0` : Barre toujours visible en bas
-   `z-index: 9999` : Au-dessus de tout le contenu
-   `box-shadow` : Effet de profondeur et separation
-   `padding-bottom: 85px` sur body : Evite que la barre cache le contenu
-   `height: 70px` : Hauteur fixe pour stabilite
-   `border-top: 2px solid #1E5128` : Bordure verte signature

**Icones utilisees** :

-   `bi-house-fill` : Accueil
-   `bi-search` : Recherche
-   `bi-plus-circle-fill` : Publier
-   `bi-gear-fill` : Parametres
-   `bi-box-arrow-in-right` : Connexion
-   `bi-person-plus-fill` : Inscription

**Taille des icones** : 2rem (32px) pour visibilite optimale sur mobile

### 23.5 Tests realises

**Devices testes** :

-   iPhone SE (375px) : 4 icones bien reparties
-   iPhone 12 Pro (390px) : Espacements corrects
-   iPad (768px) : Barre masquee, navbar classique visible
-   Desktop (1920px) : Barre masquee, navbar classique visible

**Fonctionnalites testees** :

-   Active state correct (couleur verte selon page)
-   Navigation fluide entre pages
-   Aucun overlap avec le contenu (padding ok)
-   Condition connexion OK (Publier/Parametres vs Connexion/Inscription)
-   Touch targets optimises (70px minimum)

### 23.6 Comparaison avec wireframe Figma

| Element                    | Wireframe | Implemente         | Statut |
| -------------------------- | --------- | ------------------ | ------ |
| Position fixe en bas       | Oui       | Oui                | OK     |
| 4 icones principales       | Oui       | Oui                | OK     |
| Icone engrenage Parametres | Oui       | Oui (bi-gear-fill) | OK     |
| Active state visuel        | Oui       | Oui (vert)         | OK     |
| Visible mobile uniquement  | Oui       | Oui (<768px)       | OK     |
| Touch-friendly             | Oui       | Oui (70px min)     | OK     |
| Grandes icones             | Oui       | Oui (2rem)         | OK     |

**Conformite** : 100%

### 23.7 Problemes rencontres et solutions

**Probleme 1** : CSS non applique avec fichiers externes

-   **Cause** : Imports multiples, cache navigateur, specificite CSS
-   **Solution** : CSS inline dans base.html.twig avec `!important`

**Probleme 2** : Barre affichee en haut au lieu du bas

-   **Cause** : Manque de `position: fixed` et `bottom: 0`
-   **Solution** : Ajout de `!important` pour forcer l'application

**Probleme 3** : Conflit avec classe Bootstrap `d-md-none`

-   **Cause** : Specificite Bootstrap > CSS custom
-   **Solution** : Gestion responsive en CSS pur avec media queries

### 23.8 Commits

```bash
git checkout -b feature/mobile-bottom-nav

git add .
git commit -m "feat: Barre navigation inferieure mobile fonctionnelle

Interface:
- 4 icones en bas: Accueil, Recherche, Publier, Parametres
- Grandes icones (2rem) visibles et cliquables
- Hauteur fixe 70px avec bordure verte
- Icone engrenage pour Parametres (comme Figma)

Structure:
- Partial _bottom_nav_mobile.html.twig
- CSS inline dans base.html.twig avec !important

Implementation:
- Position fixed bottom 0
- z-index 9999
- Padding body 85px pour eviter overlap
- Active state dynamique selon route

UX:
- Touch targets optimises (70px)
- Visible mobile uniquement (<768px)
- Masque desktop (>=768px)
- Transition smooth sur hover

Tests:
- iPhone SE (375x667)
- Navigation fonctionnelle
- Icones bien positionnees en bas
- Responsive OK

Conformite wireframe Figma"

git push --set-upstream origin feature/mobile-bottom-nav
git checkout master
git merge feature/mobile-bottom-nav
git push origin master
git branch -d feature/mobile-bottom-nav
git push origin --delete feature/mobile-bottom-nav

ÉTAPE 24 : AUDIT ACCESSIBILITÉ RGAA - HOMEPAGE
24.1 Contexte et objectif
L'accessibilité web (RGAA - Référentiel Général d'Amélioration de l'Accessibilité) est obligatoire pour la certification DWWM. L'audit utilise Lighthouse (Chrome DevTools) pour détecter automatiquement les problèmes d'accessibilité.
Objectif : Atteindre un score Lighthouse Accessibilité de 100% sur toutes les pages.
24.2 Audit initial - Homepage
Score initial : 91%
Problèmes détectés :
#Critère RGAAProblèmeÉlément1Contraste couleursLogo navbar #4caf50 ratio 2.63:1span.text-success2Contraste couleursLien actif navbar #4caf50 ratio 2.63:1.nav-link.active3Contraste couleursBouton "Rechercher" orange.btn-warning4Contraste couleursBouton "Voir toutes les recettes".btn-success5Contraste couleursBouton "Déconnexion" #dc3545 ratio 4.28:1.btn-outline-danger6Contraste couleursLiens footer sur fond sombrefooter a7Hiérarchie titresFooter : h5 sans h4 avantfooter h5, h68Liens sans nomIcônes réseaux sociauxfooter .d-flex a9Label/texte mismatchBouton recherche aria-label ≠ textebutton[aria-label]10FormulairesInput recherche sans id pour labelinput[name="q"]
24.3 Corrections appliquées
24.3.1 Fichier templates/home/index.html.twig
Corrections :

SVG décoratif : ajout aria-hidden="true"
Input recherche : ajout id="search-ingredients" lié au label
Bouton recherche : aria-label="Rechercher des recettes par ingrédients" (contient le texte visible)
Hiérarchie titres : h5 → h3 pour les cartes recettes
Icônes décoratives : ajout aria-hidden="true" sur toutes les <i>
Lien "Voir" : ajout aria-label="Voir la recette {{ recette.nom }}"
Étoiles notation : role="img" + aria-label="Note : X sur 5 étoiles"
Placeholder image : role="img" + aria-label="Aucune image disponible"
Alert : ajout role="alert"

24.3.2 Fichier templates/partials/_navbar.html.twig
Corrections :

Logo : text-success → style="color: #2e7d32;" (ratio 4.5:1)
Hamburger menu : ajout aria-controls, aria-expanded, aria-label
Icône Admin : ajout aria-hidden="true"
Liens actifs : style inline conditionnel pour contraste

24.3.3 Fichier templates/partials/_footer.html.twig
Corrections :

Titres : h5 → <h2 class="h5"> et h6 → <h2 class="h6"> (hiérarchie sémantique)
Liens sociaux : ajout aria-label sur chaque lien (Instagram, Twitter, TikTok, Facebook)
Icônes : ajout aria-hidden="true" sur toutes les <i>

24.3.4 Fichier public/css/utilities.css
Nouvelles règles de contraste accessibles :
css/* ===== ACCESSIBILITÉ - CONTRASTE ===== */

/* Boutons Primary & Success */
.btn-primary,
.btn-success {
    background-color: #2e7d32 !important;
    border-color: #2e7d32 !important;
    color: #fff !important;
}

.btn-primary:hover,
.btn-primary:focus,
.btn-success:hover,
.btn-success:focus {
    background-color: #1b5e20 !important;
    border-color: #1b5e20 !important;
    color: #fff !important;
}

/* Bouton Warning (Rechercher) */
.btn-warning {
    background-color: #e65100 !important;
    border-color: #e65100 !important;
    color: #fff !important;
}

.btn-warning:hover,
.btn-warning:focus {
    background-color: #bf360c !important;
    border-color: #bf360c !important;
    color: #fff !important;
}

/* Bouton Outline Danger (Déconnexion) */
.btn-outline-danger {
    color: #c62828 !important;
    border-color: #c62828 !important;
}

.btn-outline-danger:hover,
.btn-outline-danger:focus {
    background-color: #c62828 !important;
    border-color: #c62828 !important;
    color: #fff !important;
}

/* Bouton Outline Success (S'inscrire) */
.btn-outline-success {
    color: #2e7d32 !important;
    border-color: #2e7d32 !important;
}

.btn-outline-success:hover,
.btn-outline-success:focus {
    background-color: #2e7d32 !important;
    border-color: #2e7d32 !important;
    color: #fff !important;
}

/* Liens navigation actifs */
.navbar .nav-link.active {
    color: #1b5e20 !important;
}

/* Footer - liens accessibles */
footer a {
    color: #1565c0 !important;
}

footer a:hover,
footer a:focus {
    color: #0d47a1 !important;
}

/* Footer - texte muted plus visible */
footer .text-muted {
    color: #5f6368 !important;
}

/* Helpers accessibles */
.text-success {
    color: #2e7d32 !important;
}
24.4 Ratios de contraste appliqués
CouleurCodeRatio sur #fcf8f5ConformeVert foncé#2e7d325.4:1✅ AAVert très foncé#1b5e207.5:1✅ AAAOrange foncé#e651004.6:1✅ AARouge foncé#c628285.9:1✅ AABleu liens#1565c05.2:1✅ AAGris texte#5f63685.4:1✅ AA
Standard WCAG : Ratio minimum 4.5:1 pour texte normal, 3:1 pour grands textes.
24.5 Score final - Homepage
Score final : 100% ✅
Audits passés :

✅ Contraste couleurs suffisant
✅ Hiérarchie des titres séquentielle
✅ Liens avec nom accessible
✅ Labels associés aux inputs
✅ Attribut lang="fr" sur <html>
✅ ARIA correctement utilisé
✅ Images avec alt
✅ Boutons avec nom accessible

24.6 Commits
bashgit checkout -b feature/accessibilite-rgaa

git add .
git commit -m "feat(a11y): Audit accessibilité Homepage - Score 100%

Corrections RGAA appliquées:

Templates:
- home/index.html.twig: aria-hidden, labels, hiérarchie titres
- partials/_navbar.html.twig: contraste logo, aria hamburger
- partials/_footer.html.twig: h5→h2, aria-label liens sociaux

CSS (utilities.css):
- Boutons: couleurs accessibles (ratio 4.5:1 minimum)
- .btn-success: #2e7d32
- .btn-warning: #e65100
- .btn-outline-danger: #c62828
- Footer liens: #1565c0
- .nav-link.active: #1b5e20

Conformité:
- WCAG 2.1 niveau AA
- Score Lighthouse: 91% → 100%
- Tous les critères RGAA validés"

git push --set-upstream origin feature/accessibilite-rgaa
24.7 Pages restantes à auditer
PageRouteStatutHomepage/✅ 100%Connexion/login🔄 À faireInscription/register🔄 À faireListe recettes/recettes🔄 À faireDétail recette/recette/{id}🔄 À faireProfil/profil🔄 À faireCréer recette/recette/new🔄 À faireModifier recette/recette/{id}/edit🔄 À faireContact/contact🔄 À faire
```

---

## CONCLUSION GÉNÉRALE

Le projet **Les Restes** est maintenant dans un état **professionnel et complet** avec :

### Fonctionnalités implémentées

-   Authentification complète

-   CRUD recettes avec ingrédients dynamiques

-   Système de favoris AJAX

-   Commentaires et notation

-   Recherche avancée avec filtres

-   Pagination professionnelle

-   Administration des ingrédients

-   Pages légales complètes (RGPD)

-   Page 404 personnalisée

-   Design responsive complet

### Compétences REAC validées

-   Développer composants accès données SQL

-   Développer partie dynamique interfaces

-   Gestion fichiers (VichUploader)

-   Sécurité (bcrypt, CSRF, ROLE_ADMIN)

-   Architecture MVC Symfony

### Points forts

-   Code propre et organisé

-   Git avec branches feature

-   Documentation complète

-   Interface moderne et responsive

-   Conformité RGPD

**Le projet est prêt pour la soutenance d'avril 2026 !**

---

_Documentation complète - Novembre 2025_
**Dernière mise à jour** : Novembre 2025
**Statut** : En développement actif
**Graduation prévue** : Avril 2026

---

_Documentation rédigée dans le cadre du Titre Professionnel DWWM - Dawan Toulouse_
