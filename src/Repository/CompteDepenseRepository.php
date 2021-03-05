<?php

namespace App\Repository;

use App\Entity\CompteDepense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompteDepense|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompteDepense|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompteDepense[]    findAll()
 * @method CompteDepense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompteDepenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompteDepense::class);
    }

    // /**
    //  * @return CompteDepense[] Returns an array of CompteDepense objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CompteDepense
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
