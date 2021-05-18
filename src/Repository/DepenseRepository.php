<?php

namespace App\Repository;

use App\Entity\Depense;
use App\Data\DepenseSearchData;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Depense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Depense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Depense[]    findAll()
 * @method Depense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepenseRepository extends ServiceEntityRepository
{

  private $security;

  public function __construct(ManagerRegistry $registry, Security $security)
  {
    $this->security = $security;
    parent::__construct($registry, Depense::class);
  }

  /**
   * Récupère les dépense en lien avec une recherche
   */
  public function findByFilter(DepenseSearchData $search)
  {

    $query = $this
      ->createQueryBuilder('d')
      ->select('c', 'd', 'cd')
      ->join('d.categorie', 'c')
      ->join('d.compteDepense', 'cd')
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

    if (!empty($search->categories)) {
      $query = $query
        ->andWhere('c.id IN (:categories)')
        ->setParameter('categories', $search->categories);
    }
    $query->orderBy('d.date', 'DESC');

    return $query->getQuery()->getResult();
  }


  public function findDepenseForMonth(DepenseSearchData $search)
  {

    $query = $this
      ->createQueryBuilder('d')
      ->select('SUM(d.montant) as depenseMonth')
      ->join('d.compteDepense', 'cd')
      ->andWhere('cd.utilisateur = :utilisateur')
      ->andWhere('d.categorie <> 13')
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

    return $query->getQuery()->getSingleResult();
  }

  public function findVersement(DepenseSearchData $search)
  {

    $query = $this
      ->createQueryBuilder('d')
      ->select('-SUM(d.montant) as value')
      ->join('d.compteDepense', 'cd')
      ->andWhere('cd.utilisateur = :utilisateur')
      ->andWhere('d.categorie = 13')
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

    return $query->getQuery()->getSingleResult();
  }

  public function findDepenseAfterDate(DepenseSearchData $search)
  {

    $query = $this
      ->createQueryBuilder('d')
      ->select('SUM(d.montant) as depenseTotal')
      ->join('d.compteDepense', 'cd')
      ->andWhere('cd.utilisateur = :utilisateur')
      ->setParameter('utilisateur', $this->security->getUser());

    if (!empty($search->date)) {
      $month = (int) $search->date->format('m');
      $year = (int) $search->date->format('Y');

      $startDate = new \DateTimeImmutable("$year-$month-01T00:00:00");
      $endDate = $startDate->modify('last day of this month')->setTime(23, 59, 59);

      $query = $query
        ->andWhere('d.date > :end')
        ->setParameter('end', $endDate);
    }

    return $query->getQuery()->getSingleResult();
  }

  public function findDepenseCourseSumByMonth()
  {

    $query = $this
      ->createQueryBuilder('d')
      ->select('SUM(d.montant) as depenseTotal, Month(d.date) as date_month, Year(d.date) as date_year')
      ->join('d.compteDepense', 'cd')
      ->join('d.categorie', 'c')
      ->andWhere('cd.utilisateur = :utilisateur')
      ->setParameter('utilisateur', $this->security->getUser())
      ->andWhere('c.id = :categorie')
      ->setParameter('categorie', 1)
      ->andWhere('d.date < :currentMonth')
      ->setParameter('currentMonth', new DateTime('first day of this month 00:00:00'))
      ->addGroupBy('date_month, date_year');

    return $query->getQuery()->getResult();
  }

  public function findDepenseCourseAvgByMonthTotal()
  {

    $moyennes = $this->findDepenseCourseSumByMonth();
    $result = 0;

    if (sizeof($moyennes) > 0) {
      foreach ($moyennes as $moyenne) {
        $result += $moyenne["depenseTotal"];
      }

      return round($result / sizeof($moyennes), 2);
    } else {
      return 0;
    }
  }

  public function findDepenseAvgByCourse()
  {

    $query = $this
      ->createQueryBuilder('d')
      ->select('AVG(d.montant) as depenseCourseAvg')
      ->join('d.compteDepense', 'cd')
      ->join('d.categorie', 'c')
      ->andWhere('cd.utilisateur = :utilisateur')
      ->setParameter('utilisateur', $this->security->getUser())
      ->andWhere('c.id = :categorie')
      ->setParameter('categorie', 1)
      ->andWhere('d.date < :currentMonth')
      ->setParameter('currentMonth', new DateTime('first day of this month 00:00:00'));

    return round($query->getQuery()->getSingleResult()['depenseCourseAvg'], 2);
  }



  // /**
  //  * @return Depense[] Returns an array of Depense objects
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
    public function findOneBySomeField($value): ?Depense
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
