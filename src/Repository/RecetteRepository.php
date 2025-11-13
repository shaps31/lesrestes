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

     /**
     * @param int[] $ingredientIds
     * @return Recette[]
     */
    public function findByIngredients(array $ingredientIds, bool $requireAll = false): array
{
    if (empty($ingredientIds)) {
        return [];
    }
    
    $qb = $this->createQueryBuilder('r')
        ->innerJoin('r.recetteIngredients', 'ri') 
        ->innerJoin('ri.ingredient', 'i')
        ->where('i.id IN (:ingredientIds)')
        ->setParameter('ingredientIds', $ingredientIds)
        ->groupBy('r.id');
    
    if ($requireAll) {
        $qb->having('COUNT(DISTINCT i.id) = :count')
           ->setParameter('count', count($ingredientIds));
    }
    
    return $qb->orderBy('r.dateCreation', 'DESC')
        ->getQuery()
        ->getResult();
}   

    public function findWithFilters(array $criteria = [], array $orderBy = []): array
{
    $qb = $this->createQueryBuilder('r')
        ->leftJoin('r.commentaires', 'c')
        ->leftJoin('r.recetteIngredients', 'ri')
        ->leftJoin('ri.ingredient', 'i')
        ->groupBy('r.id');
    
    if (isset($criteria['query'])) {
        $qb->andWhere('r.nom LIKE :query OR i.nom LIKE :query')
           ->setParameter('query', '%' . $criteria['query'] . '%');
    }
    
    if (isset($criteria['categorie'])) {
        $qb->andWhere('r.categorie = :categorie')
           ->setParameter('categorie', $criteria['categorie']);
    }
    
    if (isset($criteria['difficulte'])) {
        $qb->andWhere('r.difficulte = :difficulte')
           ->setParameter('difficulte', $criteria['difficulte']);
    }
    
    if (isset($criteria['tempsMax'])) {
        $qb->andWhere('r.tempsCuisson <= :tempsMax')
           ->setParameter('tempsMax', $criteria['tempsMax']);
    }
    
    foreach ($orderBy as $field => $direction) {
        if ($field === 'moyenneNotes') {
            $qb->addSelect('AVG(c.note) as HIDDEN avg_note')
               ->addOrderBy('avg_note', $direction);
        } else {
            $qb->addOrderBy('r.' . $field, $direction);
        }
    }
    
         return $qb->getQuery()->getResult();
    }
    public function findWithFiltersQueryBuilder(array $criteria = [], array $orderBy = [])
{
    $qb = $this->createQueryBuilder('r')
        ->leftJoin('r.commentaires', 'c')
        ->leftJoin('r.recetteIngredients', 'ri')
        ->leftJoin('ri.ingredient', 'i')
        ->groupBy('r.id');
    
    if (isset($criteria['query'])) {
        $qb->andWhere('r.nom LIKE :query OR i.nom LIKE :query')
           ->setParameter('query', '%' . $criteria['query'] . '%');
    }
    
    if (isset($criteria['categorie'])) {
        $qb->andWhere('r.categorie = :categorie')
           ->setParameter('categorie', $criteria['categorie']);
    }
    
    if (isset($criteria['difficulte'])) {
        $qb->andWhere('r.difficulte = :difficulte')
           ->setParameter('difficulte', $criteria['difficulte']);
    }
    
    if (isset($criteria['tempsMax'])) {
        $qb->andWhere('r.tempsCuisson <= :tempsMax')
           ->setParameter('tempsMax', $criteria['tempsMax']);
    }
    
    foreach ($orderBy as $field => $direction) {
        if ($field === 'moyenneNotes') {
            $qb->addSelect('AVG(c.note) as HIDDEN avg_note')
               ->addOrderBy('avg_note', $direction);
        } else {
            $qb->addOrderBy('r.' . $field, $direction);
        }
    }
    
    return $qb;
}
public function findByNameOrIngredients(string $searchTerm, array $ingredientIds = []): array
{
    $qb = $this->createQueryBuilder('r')
        ->leftJoin('r.recetteIngredients', 'ri')
        ->leftJoin('ri.ingredient', 'i')
        ->where('r.nom LIKE :searchTerm')
        ->setParameter('searchTerm', '%' . $searchTerm . '%');
    
    if (!empty($ingredientIds)) {
        $qb->orWhere('i.id IN (:ingredientIds)')
           ->setParameter('ingredientIds', $ingredientIds)
           ->groupBy('r.id')
           ->having('COUNT(DISTINCT i.id) = :count')
           ->setParameter('count', count($ingredientIds));
    }
    
    return $qb->orderBy('r.dateCreation', 'DESC')
        ->getQuery()
        ->getResult();
}
}
