<?php

namespace App\Repository;

use App\Data\DepenseSearchData;
use App\Entity\DepenseRecurrente;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method DepenseRecurrente|null find($id, $lockMode = null, $lockVersion = null)
 * @method DepenseRecurrente|null findOneBy(array $criteria, array $orderBy = null)
 * @method DepenseRecurrente[]    findAll()
 * @method DepenseRecurrente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepenseRecurrenteRepository extends ServiceEntityRepository
{

  private $security;

  /**
   * @var Security
   */
  public function __construct(ManagerRegistry $registry, Security $security)
  {
    $this->security = $security;
    parent::__construct($registry, DepenseRecurrente::class);
  }


  public function findDepenseRecurrenteByAccountUsed(DepenseSearchData $search)
  {

    $query = $this
      ->createQueryBuilder('dr')
      ->join('dr.compteDepense', 'cd')
      ->join('dr.depenses', 'd')
      ->andWhere('cd.utilisateur = :utilisateur')
      ->setParameter('utilisateur', $this->security->getUser());

    if (!empty($search->date)) {
      $month = (int) $search->date->format('m');
      $year = (int) $search->date->format('Y');

      $startDate = new \DateTimeImmutable("$year-$month-01T00:00:00");
      $endDate = $startDate->modify('last day of this month')->setTime(23, 59, 59);

      $query = $query
        ->andWhere('d.date BETWEEN :start AND :end')
        ->setParameter('start', $startDate)
        ->setParameter('end', $endDate);
    }


    return $query->getQuery()->getResult();
  }

  public function findDepenseRecurrenteByAccountNotUsed(DepenseSearchData $search)
  {
    $used = $this->findDepenseRecurrenteByAccountUsed($search);
    $query = $this
      ->createQueryBuilder('dr')
      ->join('dr.compteDepense', 'cd')
      ->andWhere('cd.utilisateur = :utilisateur')
      ->setParameter('utilisateur', $this->security->getUser());

    $result = array_udiff($query->getQuery()->getResult(), $used,  function (DepenseRecurrente $obj_a, DepenseRecurrente $obj_b) {
      return strcmp($obj_a->getId(), $obj_b->getId());
    });

    return $result;
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
