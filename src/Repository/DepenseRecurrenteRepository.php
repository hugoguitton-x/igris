<?php

namespace App\Repository;

use App\Entity\DepenseRecurrente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DepenseRecurrente|null find($id, $lockMode = null, $lockVersion = null)
 * @method DepenseRecurrente|null findOneBy(array $criteria, array $orderBy = null)
 * @method DepenseRecurrente[]    findAll()
 * @method DepenseRecurrente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepenseRecurrenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DepenseRecurrente::class);
    }

    // /**
    //  * @return DepenseRecurrente[] Returns an array of DepenseRecurrente objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DepenseRecurrente
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
