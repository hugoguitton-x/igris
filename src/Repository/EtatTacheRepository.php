<?php

namespace App\Repository;

use App\Entity\EtatTache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method EtatTache|null find($id, $lockMode = null, $lockVersion = null)
 * @method EtatTache|null findOneBy(array $criteria, array $orderBy = null)
 * @method EtatTache[]    findAll()
 * @method EtatTache[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtatTacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EtatTache::class);
    }

    // /**
    //  * @return EtatTache[] Returns an array of EtatTache objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EtatTache
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
