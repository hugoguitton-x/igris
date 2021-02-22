<?php

namespace App\DataFixtures;

use App\Entity\Avis;
use App\Entity\Serie;
use App\Entity\Utilisateur;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SerieAvisFixtures extends Fixture implements FixtureGroupInterface
{
  private $encoder;

  public function __construct(UserPasswordEncoderInterface $encoder)
  {
    $this->encoder = $encoder;
  }

  public function load(ObjectManager $manager)
  {
/*
    $faker = \Faker\Factory::create('fr_FR');

    $tempon = 1;
    for ($i = 0; $i <= mt_rand(5, 15); $i++) {
      $serie = new Serie();
      $serie->setNom($faker->sentence());
      $serie->setImage('faker.jpg');
      $serie->setSynopsis(join($faker->sentences(5), '<br/>'));
      $serie->setLien($faker->url());
      $serie->setNombreEpisodes($faker->numberBetween(1, 25));
      $serie->setDureeEpisode($faker->numberBetween(20, 75));
      $serie->setCreatedAt($faker->dateTimeBetween('-50 months'));
      $serie->setNoteMoyenne(5.5);

      $manager->persist($serie);

      $total = 0;
      for ($v = 1; $v <= 10; $v++) {
        $utilisateur = new Utilisateur();
        $u = $v + $tempon;
        $utilisateur->setUsername("tatsuki$u");
        $password = $this->encoder->encodePassword(
          $utilisateur,
          '123456789'
        );
        $utilisateur->setPassword($password);
        $utilisateur->setEmail($faker->email());
        $utilisateur->setFirstName($faker->firstName());
        $utilisateur->setLastName($faker->lastName());
        $utilisateur->setAvatar('default.png');
        $utilisateur->setRoles(array('ROLE_USER'));

        $manager->persist($utilisateur);

        $avis = new Avis();
        $avis->setUtilisateur($utilisateur);
        $avis->setSerie($serie);
        $avis->setCommentaire(join($faker->sentences(2), '<br/>'));
        $avis->setNote($faker->numberBetween(0, 10));
        $avis->setCreatedAt($faker->dateTimeBetween('-50 months'));

        $manager->persist($avis);

        $total += $avis->getNote();
        $tempon++;
      }

      $serie->setNoteMoyenne(round($total / 10, 2));
      $manager->persist($serie);
    }

    $manager->flush(); */
  }

  public static function getGroups(): array
  {
    return ['serie_avis'];
  }
}
