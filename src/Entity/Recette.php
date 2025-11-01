<?php

namespace App\Entity;

use App\Repository\RecetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecetteRepository::class)]
class Recette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $temps_preparation = null;

    #[ORM\Column(length: 50)]
    private ?string $difficulte = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_creation = null;

    #[ORM\ManyToOne(inversedBy: 'recettes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

   

    /**
     * @var Collection<int, RecetteIngredient>
     */
   
    #[ORM\OneToMany(targetEntity: RecetteIngredient::class, mappedBy: 'recette', orphanRemoval: true)]
    private Collection $recetteIngredients; 
    
    /**
     * @var Collection<int, Etape>
     */
    
    #[ORM\OneToMany(targetEntity: Etape::class, mappedBy: 'recette', orphanRemoval: true)]
    private Collection $etapes;

    
    // * @var Collection<int, Ingredient>
    // #[ORM\ManyToMany(targetEntity: Ingredient::class, mappedBy: 'recettes')]
    // private Collection $ingredients;

    

    public function __construct()
    {
        // $this->ingredients = new ArrayCollection(); // Ne sert plus, la collection est dans RecetteIngredient
        $this->recetteIngredients = new ArrayCollection();
        $this->etapes = new ArrayCollection(); // Nouvelle collection
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getTempsPreparation(): ?int { return $this->temps_preparation; }
    public function setTempsPreparation(int $temps_preparation): static { $this->temps_preparation = $temps_preparation; return $this; }

    public function getDifficulte(): ?string { return $this->difficulte; }
    public function setDifficulte(string $difficulte): static { $this->difficulte = $difficulte; return $this; }

    public function getDateCreation(): ?\DateTimeImmutable { return $this->date_creation; }
    public function setDateCreation(\DateTimeImmutable $date_creation): static { $this->date_creation = $date_creation; return $this; }

    public function getCategorie(): ?Categorie { return $this->categorie; }
    public function setCategorie(?Categorie $categorie): static { $this->categorie = $categorie; return $this; }

    // --- Méthodes pour RecetteIngredient (Anciennement $Recette) ---

    /**
     * @return Collection<int, RecetteIngredient>
     */
    public function getRecetteIngredients(): Collection
    {
        return $this->recetteIngredients;
    }

    public function addRecetteIngredient(RecetteIngredient $recetteIngredient): static
    {
        if (!$this->recetteIngredients->contains($recetteIngredient)) {
            $this->recetteIngredients->add($recetteIngredient);
            $recetteIngredient->setRecette($this);
        }

        return $this;
    }

    public function removeRecetteIngredient(RecetteIngredient $recetteIngredient): static
    {
        if ($this->recetteIngredients->removeElement($recetteIngredient)) {
            // set the owning side to null (unless already changed)
            if ($recetteIngredient->getRecette() === $this) {
                $recetteIngredient->setRecette(null);
            }
        }

        return $this;
    }
    
    // --- Méthodes pour Etape ---

    /**
     * @return Collection<int, Etape>
     */
    public function getEtapes(): Collection
    {
        return $this->etapes;
    }

    public function addEtape(Etape $etape): static
    {
        if (!$this->etapes->contains($etape)) {
            $this->etapes->add($etape);
            $etape->setRecette($this);
        }

        return $this;
    }

    public function removeEtape(Etape $etape): static
    {
        if ($this->etapes->removeElement($etape)) {
            // set the owning side to null (unless already changed)
            if ($etape->getRecette() === $this) {
                $etape->setRecette(null);
            }
        }

        return $this;
    }
}