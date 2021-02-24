<?php

namespace App\tests\Repository;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\UserAdminFixtures;
use App\Repository\UtilisateurRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UtilisateurRepositoryTest extends KernelTestCase
{

  use FixturesTrait;

  public function testCreateUser()
  {
    $this->loadFixtures([UserFixtures::class]);
    $users = static::$container->get(UtilisateurRepository::class)->count([]);

    $this->assertEquals(10, $users);
  }

  public function testCreateAdmin()
  {
    $this->loadFixtures([UserAdminFixtures::class], true);
    $users = static::$container->get(UtilisateurRepository::class)->findByRole('admin');

    $this->assertEquals(5, count($users));
  }
}
