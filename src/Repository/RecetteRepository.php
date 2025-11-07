<?php

namespace App\Repository;

use App\Entity\Recette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recette>
 */
class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    public function findByIngredients(array $ingredientIds): array
{
    if (empty($ingredientIds)) {
        return [];
    }
    
    $qb = $this->createQueryBuilder('r')
        ->leftJoin('r.user', 'u')
        ->addSelect('u');
    
    // Pour chaque ingrédient, on ajoute une jointure
    foreach ($ingredientIds as $index => $ingredientId) {
        $qb->leftJoin('r.recetteIngredients', 'ri' . $index)
           ->leftJoin('ri' . $index . '.ingredient', 'i' . $index);
    }
    
    // Condition WHERE pour qu'au moins un des ingrédients soit présent
    $conditions = [];
    foreach ($ingredientIds as $index => $ingredientId) {
        $conditions[] = 'i' . $index . '.id = :ingredient' . $index;
        $qb->setParameter('ingredient' . $index, $ingredientId);
    }
    
    $qb->where(implode(' OR ', $conditions))
       ->groupBy('r.id')
       ->orderBy('r.dateCreation', 'DESC');
    
    return $qb->getQuery()->getResult();
}
}
