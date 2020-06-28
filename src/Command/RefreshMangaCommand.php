<?php

namespace App\Command;

use App\Entity\Manga;
use App\Entity\Language;
use App\Entity\LastChapter;
use App\Repository\MangaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshMangaCommand extends Command
{
    protected static $defaultName = 'app:refresh-manga';

    protected $manager;
    protected $mangaRepo;

    public function __construct(EntityManagerInterface $manager, MangaRepository $mangaRepo)
    {
        $this->manager = $manager;
        $this->mangaRepo = $mangaRepo;
 
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Met à jour les informations des mangas')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeln('Récupération de l\'ensemble des mangas.');
        $mangas = $this->mangaRepo->findAll();
        $output->writeln('=======================================');

        $output->writeln('Mise à jour de l\'ensemble des informations des mangas.');
        $output->writeln('=======================================');
        foreach ($mangas as $manga) {
            $this->refresh($manga->getRss(), $this->manager, $output);
        }
        

        $io->success('La liste des mangas a bien été mise à jour !');

        return 0;
    }

    private function refresh(string $rss, EntityManagerInterface $manager, OutputInterface $output)
    {
        $mangaRepo = $manager->getRepository(Manga::class);
        $languageRepo = $manager->getRepository(Language::class);
        $lastChapterRepo = $manager->getRepository(LastChapter::class);
        $feedIo = \FeedIo\Factory::create()->getFeedIo();
        $result = $feedIo->read($rss);

        $rss_explode = explode('/', parse_url($rss, PHP_URL_PATH));
        $url = 'https://mangadex.org/title/' . end($rss_explode);

        $mangaArray = array(
            'name' => '',
            'url' => $url,
            'rss' => $rss,
            'by_language' => array()
        );
        
        $language = '';

        foreach ($result->getFeed() as $item) {
            if (empty($mangaArray['name'])) {
                if(strpos(strtolower(explode('-', $item->getTitle())[1]), 'volume') === false || strpos(strtolower(explode('-', $item->getTitle())[1]), 'chapter') === false){
                    $mangaArray['name'] = trim(substr($item->getTitle(), 0, strpos($item->getTitle(), 'Chapter') - 2));
                } else {
                    $mangaArray['name']  = trim(explode('-', $item->getTitle())[0]);
                }
            }

            $description = $item->getDescription();

            $language = substr(
                $description,
                strpos($description, 'Language') + 10
            );

            if($language){
                if (!array_key_exists($language, $mangaArray['by_language'])) {
                    $mangaArray['by_language'][$language] = array(
                        'last_chapter' => 0,
                        'date' => ''
                    );
                }
            }

            $chapter =  trim(substr($item->getTitle(), strpos($item->getTitle(), 'Chapter') + 7));

            if (
                $mangaArray['by_language'][$language]['last_chapter'] <
                floatval($chapter)
            ) {
                $mangaArray['by_language'][$language]['last_chapter'] =
                    $chapter === intval($chapter)
                        ? floatval($chapter)
                        : intval($chapter);

                $mangaArray['by_language'][$language][
                    'date'
                ] = $item->getLastModified()->format('Y-M-d');
            }
        }

        $manga = $mangaRepo->findOneBy(array(
            'name' => $mangaArray['name']
        ));

        if (!$manga) {
            $manga = new Manga();
            $manga->setName($mangaArray['name']);
            $manga->setUrl($mangaArray['url']);
            $manga->setRss($mangaArray['rss']);
            $manga->setImage('');
            $manager->persist($manga);
        }

        foreach ($mangaArray['by_language'] as $langue => $chapter) {
            $langue = ucfirst(strtolower($langue));
            $language = $languageRepo->findOneBy(array(
                'name' => $langue
            ));

            if (!$language) {
                $language = new Language();
                $language->setName($langue);
                $manager->persist($language);
            }

            $lastChapterDB = $lastChapterRepo->findOneBy(array(
                'language' => $language,
                'manga' => $manga
            ));

            if (
                $lastChapterDB !== null &&
                $chapter['last_chapter'] !== $lastChapterDB->getNumber()
            ) {
                $lastChapterDB->setNumber($chapter['last_chapter']);
                $lastChapterDB->setDate(new \DateTime($chapter['date']));
            } else {
                $lastChapter = new LastChapter();
                $lastChapter->setNumber($chapter['last_chapter']);
                $lastChapter->setDate(new \DateTime($chapter['date']));
                $lastChapter->setLanguage($language);
                $lastChapter->setManga($manga);
                $manager->persist($lastChapter);
            }
            if( $lastChapterDB !== null &&
            $chapter['last_chapter'] !== $lastChapterDB->getNumber()){
                $output->writeln($manga->getName().' - Langue : ' . $language->getName() . ' - Chapitre n°'.$lastChapterDB->getNumber());
            } else {
                $output->writeln($manga->getName().' - Langue : ' . $language->getName() . ' - Chapitre n°'.$lastChapter->getNumber());
            }
            $manager->flush();
            
        }
    }
}
