<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{

  private $encoder;

  public function __construct(UserPasswordEncoderInterface $encoder)
  {
    $this->encoder = $encoder;
  }

  public function load(ObjectManager $manager)
  {
    $faker = \Faker\Factory::create('fr_FR');

    for($i = 0; $i < 10; $i++) {
      $utilisateur = new Utilisateur();
        $utilisateur->setUsername($faker->userName());
        $password = $this->encoder->encodePassword(
          $utilisateur,
          $faker->password()
        );
        $utilisateur->setPassword($password);
        $utilisateur->setEmail($faker->safeEmail());
        $utilisateur->setFirstName($faker->firstName());
        $utilisateur->setLastName($faker->lastName());
        $utilisateur->setAvatar($faker->imageUrl(640, 480, 'animals', true));
        $utilisateur->setRoles(array('ROLE_USER'));

        $manager->persist($utilisateur);
    }

    $manager->flush();
  }


  public static function getGroups(): array
  {
    return ['utilisateurs'];
  }
}
