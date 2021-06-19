<?php

namespace App\Repository;

use App\Entity\FollowManga;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FollowManga|null find($id, $lockMode = null, $lockVersion = null)
 * @method FollowManga|null findOneBy(array $criteria, array $orderBy = null)
 * @method FollowManga[]    findAll()
 * @method FollowManga[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FollowMangaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FollowManga::class);
    }

    // /**
    //  * @return FollowManga[] Returns an array of FollowManga objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FollowManga
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
