<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Utilisateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Utilisateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Utilisateur[]    findAll()
 * @method Utilisateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * @return Utilisateur[] Returns an array of Utilisateur objects (Par Nom)
     */
    public function getUtilisateurAdminInfosOrderByUsernameQuery()
    {
        return $this->createQueryBuilder('u')
            ->select('u.id, u.username, u.email, u.lastLogin, u.roles, u.avatar')
            ->orderBy('u.username', 'ASC')
            ->getQuery();
    }

    /**
     * @return Utilisateur[] Returns an array of Utilisateur objects (Par Nom)
     */
    public function findByRole(string $role)
    {
        $role = mb_strtoupper($role);

        return $this->createQueryBuilder('u')
            ->andWhere("JSONB_HGG(u.roles, '{}') LIKE :role")
            ->setParameter('role', '%ROLE_' . $role . '%')
            ->getQuery()
            ->getResult();
    }


    // /**
    //  * @return Utilisateur[] Returns an array of Utilisateur objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Utilisateur
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
