<?php

namespace App\Repository;

use App\Entity\Chapter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Chapter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chapter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chapter[]    findAll()
 * @method Chapter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChapterRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Chapter::class);
  }

  /**
   * @return Chapter[] Returns an array of Chapter objects (Par langue si en paramètre)
   */
  public function findLastChapterOrderByDateQuery($language = null)
  {
    $conn = $this->getEntityManager()->getConnection();

    $sql = 'SELECT m.name, m.image, m.manga_id, lc.lang_code, lc.libelle, c.number, c.chapter_id, c.published_at
        FROM manga m
        LEFT JOIN
        (
            SELECT c.manga_id, c.lang_code_id, MAX(c.number) as chapter_number
            FROM chapter c
            GROUP BY (c.manga_id, c.lang_code_id)
        ) cs
        ON cs.manga_id = m.id
        LEFT JOIN language_code lc
        ON lc.id = cs.lang_code_id
        LEFT JOIN chapter c
        ON c.manga_id = cs.manga_id AND c.lang_code_id = cs.lang_code_id AND c.number = cs.chapter_number
        WHERE cs.chapter_number is not null ';

    if ($language) {
      $sql .= 'AND lc.libelle = :language ';
    }

    $sql .= 'ORDER BY c.published_at DESC';

    $query = $conn->prepare($sql);
    if ($language) {
      $query->executeQuery(['language' => $language]);
    } else {
      $query->executeQuery();
    }


    return $query->fetchAllAssociative();
  }

  // /**
  //  * @return Chapter[] Returns an array of Chapter objects
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
    public function findOneBySomeField($value): ?Chapter
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
