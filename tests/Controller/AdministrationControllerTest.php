<?php

namespace App\tests\Controller;

use App\Repository\UtilisateurRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdministrationControllerTest extends WebTestCase
{

  /**
   * Undocumented function
   *
   * @dataProvider provideUrls
   * @return void
   */
  public function testVisitingWhileLoggedIn($url)
  {
    $client = static::createClient();

    $userRepository = static::$container->get(UtilisateurRepository::class);

    // retrieve the test users
    $testUsers = $userRepository->findAll();
    foreach ($testUsers as $testUser) {
      // simulate $testUser being logged in
      $client->loginUser($testUser);

      $client->request('GET', $url);

      $this->assertResponseIsSuccessful();
    }
  }

  /**
   * Undocumented function
   *
   * @dataProvider provideAdminUrls
   * @return void
   */
  public function testVisitingWhileLoggedInAsAdmin($url)
  {
    $client = static::createClient();

    $userRepository = static::$container->get(UtilisateurRepository::class);

    // retrieve the test users
    $testUsers = $userRepository->findByRole('admin');
    foreach ($testUsers as $testUser) {
      // simulate $testUser being logged in
      $client->loginUser($testUser);

      $client->request('GET', $url);

      $this->assertResponseIsSuccessful();
    }
  }

  public function provideUrls()
  {
    return [
      ['/fr'],
      ['/en'],
      ['/fr/manga'],
      ['/en/manga'],
      ['/fr/manga/chapters'],
      ['/en/manga/chapters'],
      ['/fr/serie'],
      ['/en/serie'],
    ];
  }


  public function provideAdminUrls()
  {
    return [
      ['/en/admin'],
      ['/en/admin'],
      ['/fr/admin/user'],
      ['/en/admin/user'],
      ['/fr/admin/manga/new'],
      ['/en/admin/manga/new'],
      ['/en/admin/serie/new'],
      ['/en/admin/serie/new'],
    ];
  }
}
