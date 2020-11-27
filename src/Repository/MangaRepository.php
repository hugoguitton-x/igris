<?php

namespace App\Repository;

use App\Entity\Manga;
use App\Data\MangaSearchData;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Manga|null find($id, $lockMode = null, $lockVersion = null)
 * @method Manga|null findOneBy(array $criteria, array $orderBy = null)
 * @method Manga[]    findAll()
 * @method Manga[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MangaRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
  {
    parent::__construct($registry, Manga::class);
    $this->paginator = $paginator;
  }

  /**
   * @param MangaSearchData $search
   * @return SlidingPagination
   */
  public function findMangaOrderByNameQuery(MangaSearchData $search): SlidingPagination
  {
    $query = $this->createQueryBuilder('m')
      ->orderBy('m.name', 'ASC');

    if (!empty($search->q)) {
      $query = $query->andWhere('LOWER(m.name) LIKE LOWER(:q)')->setParameter('q', "%{$search->q}%");
    }


    $query = $query->getQuery();

    $pagination = $this->paginator->paginate(
      $query,
      $search->page,
      30
    );

    $pagination->setCustomParameters([
      'align' => 'center', # center|right
      'size' => 'small', # small|large
    ]);

    return $pagination;
  }

  // /**
  //  * @return Manga[] Returns an array of Manga objects
  //  */
  /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

  /*
    public function findOneBySomeField($value): ?Manga
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
