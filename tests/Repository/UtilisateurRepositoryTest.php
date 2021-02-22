<?php

namespace App\tests\Repository;

use App\DataFixtures\UtilisateurFixtures;
use App\Repository\UtilisateurRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UtilisateurRepositoryTest extends KernelTestCase
{

  use FixturesTrait;

  public function testCount()
  {
    $this->loadFixtures([UtilisateurFixtures::class]);
    $users = static::$container->get(UtilisateurRepository::class)->count([]);

    $this->assertEquals(10, $users);
  }

}
