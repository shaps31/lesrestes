<?php

namespace App\Repository;

use App\Entity\Ingredient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ingredient>
 */
class IngredientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ingredient::class);
    }
    
    /**
     * Recherche des ingrédients par nom (LIKE)
     * Gère singulier/pluriel, minuscules/majuscules
     */
    public function findByNameLike(string $search): array
    {
        return $this->createQueryBuilder('i')
            ->where('LOWER(i.nom) LIKE LOWER(:search)')
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getResult();
    }
}