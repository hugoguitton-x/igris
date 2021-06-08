<?php

namespace App\Command;

use App\Helper\TwitterHelper;
use App\Repository\MangaRepository;
use App\Helper\MangaMangadexApiHelperV5;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class RefreshInfosMangaCommand extends Command
{
  protected static $defaultName = 'app:refresh-infos-manga';

  protected $manager;
  protected $mangaRepo;

  public function __construct(EntityManagerInterface $manager, MangaRepository $mangaRepo, ParameterBagInterface $params)
  {
    $this->manager = $manager;
    $this->mangaRepo = $mangaRepo;
    $this->params = $params;
    $this->twitter = new TwitterHelper($params);

    parent::__construct();
  }

  protected function configure()
  {
    $this
      ->setDescription('Met à jour les informations des mangas');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $output->writeln('<comment>~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~</comment>');
    $output->writeln('<info>' . (new \DateTime())->format('Y-m-d H:i:s') . '</info>');
    $output->writeln('<comment>=======================================</comment>');
    $output->writeln('<comment>Récupération de l\'ensemble des mangas.</comment>');

    $mangas = $this->mangaRepo->findAll(array(), array('username' => 'ASC'));
    $countMangas = count($mangas);
    $output->writeln('<comment>=======================================</comment>');
    $output->writeln('<comment>Mise à jour de l\'ensemble des informations des mangas.</comment>');
    $output->writeln('<comment>=======================================</comment>');

    $count = 1;
    $start = microtime(true);

    $mangaMangadexApi = new MangaMangadexApiHelperV5($this->params, $this->manager, ["type" => "command", "lang" => "fr"], $output);
    foreach ($mangas as $manga) {
      $output->writeln('<comment> -- ' . $manga->getName() . ' --' . ' (' . $count . '/' . $countMangas . ') </comment>');

      $result = $mangaMangadexApi->refreshMangaById($manga->getMangadexId());
      if ($result === "updated" || $result === "created") {
        $output->writeln("<info> OK </info>");
      } else if ($result === "not_updated") {
        $output->writeln("<info> SKIP </info>");
      } else {
        $output->writeln("<error> KO : " . $result . "</error>");
      }

      $count++;
      sleep(2);
    }

    $io->success('La liste des mangas a bien été mise à jour !');
    $io->note("Temps d'exécution : " . round((microtime(true) - $start) / 60, 2) . " minutes");
    return 0;
  }
}
