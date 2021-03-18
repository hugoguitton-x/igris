<?php

namespace App\Command;

use App\Entity\Manga;
use App\Repository\MangaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RefreshImageMangaCommand extends Command
{
  protected static $defaultName = 'app:refresh-image-manga';

  protected $manager;
  protected $mangaRepo;

  public function __construct(EntityManagerInterface $manager, MangaRepository $mangaRepo, ParameterBagInterface $params)
  {
    $this->manager = $manager;
    $this->mangaRepo = $mangaRepo;
    $this->params = $params;

    parent::__construct();
  }

  protected function configure()
  {
    $this
      ->setDescription('Met à jour les images des mangas');
  }
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $output->writeln('<comment>~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~</comment>');
    $output->writeln('<info>' . (new \DateTime())->format('Y-m-d H:i:s') . '</info>');
    $output->writeln('<comment>=======================================</comment>');
    $output->writeln('<comment>Récupération de l\'ensemble des mangas.</comment>');
    $mangas = $this->mangaRepo->findAll();
    $output->writeln('<comment>=======================================</comment>');

    $output->writeln('<comment>Mise à jour de l\'ensemble des informations des mangas.</comment>');
    $output->writeln('<comment>=======================================</comment>');

    foreach ($mangas as $manga) {
      $output->writeln('<comment> -- ' . $manga->getName() . ' -- </comment>');
      $this->refreshImage($manga->getMangaId(), $this->manager, $output);
      $output->writeln('<info> OK </info>');
    }


    $io->success('Les images des mangas ont bien été mises à jour !');

    return 0;
  }

  private function refreshImage(string $mangaId, EntityManagerInterface $manager, OutputInterface $output)
  {
    $mangaRepo = $manager->getRepository(Manga::class);

    $mangadexURL = $this->params->get('api_mangadex_url');

    $client = HttpClient::create(['http_version' => '2.0']);
    $response = $client->request('GET', $mangadexURL . '/api/v2/manga/' . $mangaId);

    if ($response->getStatusCode() != 200) {
      $output->writeln("<error>API for can't be reach for this manga</error>");
    }

    $response_json = json_decode($response->getContent());
    $manga = $response_json->data;

    $mangaDB = $mangaRepo->findOneBy(array(
      'mangaId' => $mangaId,
    ));

    $mangaDB->setImage($manga->mainCover);

    $manager->persist($mangaDB);
    $manager->flush();
  }
}
