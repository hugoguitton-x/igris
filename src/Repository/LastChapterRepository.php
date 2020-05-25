<?php

namespace App\Repository;

use App\Entity\LastChapter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LastChapter|null find($id, $lockMode = null, $lockVersion = null)
 * @method LastChapter|null findOneBy(array $criteria, array $orderBy = null)
 * @method LastChapter[]    findAll()
 * @method LastChapter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LastChapterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LastChapter::class);
    }

    /**
     * @return LastChapter[] Returns an array of LastChapter objects (Par langue si en paramètre)
     */
    public function findLastChapterOrderByDateQuery($language = null)
    {
        $query = $this->createQueryBuilder('lc')
            ->orderBy('lc.date', 'DESC');

        if($language){
            $query = $query->leftJoin('lc.language', 'l')
            ->andWhere('l.name = :name')
            ->setParameter('name', $language);
        }

        return $query->getQuery();
    }


    // /**
    //  * @return LastChapter[] Returns an array of LastChapter objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LastChapter
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
