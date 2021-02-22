<?php

namespace App\tests\Controller;

use App\Repository\UtilisateurRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdministrationControllerTest extends WebTestCase
{

  public function testVisitingWhileLoggedIn()
  {
    $client = static::createClient();

    $userRepository = static::$container->get(UtilisateurRepository::class);

    // retrieve the test users
    $testUsers = $userRepository->findAll();
    foreach ($testUsers as $testUser) {
      // simulate $testUser being logged in
      $client->loginUser($testUser);

      // test e.g. the manga page
      $client->request('GET', '/fr/manga');
      $this->assertResponseIsSuccessful();

    }

    //$this->assertSelectorTextContains('h1', 'Hello John!');
  }
}
