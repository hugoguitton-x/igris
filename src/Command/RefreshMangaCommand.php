<?php

namespace App\Command;

use App\Entity\Manga;
use App\Entity\Chapter;
use App\Entity\LanguageCode;
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

class RefreshMangaCommand extends Command
{
    protected static $defaultName = 'app:refresh-manga';

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
            $this->refresh($manga->getMangaId(), $this->manager, $output);
        }
        

        $io->success('La liste des mangas a bien été mise à jour !');

        return 0;
    }

    private function refresh(string $mangaId, EntityManagerInterface $manager, OutputInterface $output)
    {
        $mangaRepo = $manager->getRepository(Manga::class);
        $langCodeRepo = $manager->getRepository(LanguageCode::class);
        $chapterRepo = $manager->getRepository(Chapter::class);

        $mangadexURL =  $this->params->get('mangadex_url');

        $client = HttpClient::create(['http_version' => '2.0']);
        $response = $client->request('GET', $mangadexURL.'/api/manga/'.$mangaId);

        if($response->getStatusCode() != 200){
            throw new \Exception('Pas de chance');
        }
 
        $data = json_decode($response->getContent());

        $manga = $data->manga;
        $chapters = $data->chapter;
 
        $mangaDB = $mangaRepo->findOneBy(array(
            'mangaId' => $mangaId,
        ));
    
        $urlImage = $mangadexURL.strtok($manga->cover_url, "?");
        $info = pathinfo($urlImage);
        $image = $info['basename'];

        if(!$mangaDB){
            $mangaDB = new Manga();
            $mangaDB->setName(htmlspecialchars_decode($manga->title));

            $imageFile = file_get_contents($urlImage);
            $file = $this->params->get('kernel.project_dir') . "/public/uploads/mangas/".$info['basename'];
            file_put_contents($file, $imageFile);

            $mangaDB->setImage($image);
            $mangaDB->setMangaId($mangaId);
            $manager->persist($mangaDB);
            $manager->flush();
        } else {
            if(!file_exists($this->params->get('kernel.project_dir') . "/public/uploads/mangas/".$info['basename'])){

                $imageFile = file_get_contents($urlImage);
                $file = $this->params->get('kernel.project_dir') . "/public/uploads/mangas/".$info['basename'];
                file_put_contents($file, $imageFile);

                $mangaDB->setImage($image);
            } else if($mangaDB->getImage() != $image){

                $imageFile = file_get_contents($urlImage);
                $file = $this->params->get('kernel.project_dir') . "/public/uploads/mangas/".$info['basename'];
                file_put_contents($file, $imageFile);

                $mangaDB->setImage($image);
            } else if(md5(file_get_contents($urlImage)) != md5(file_get_contents($this->params->get('kernel.project_dir') . "/public/uploads/mangas/".$info['basename']))) {
                $imageFile = file_get_contents($urlImage);
                $file = $this->params->get('kernel.project_dir') . "/public/uploads/mangas/".$info['basename'];
                file_put_contents($file, $imageFile);
            } 

            if($mangaDB->getMangaId() != $mangaId){
                $mangaDB->setMangaId($mangaId);
            }
      
            $manager->persist($mangaDB);
            $manager->flush();
        }

        foreach($chapters as $chapter_id => $values){
            if($values->chapter) {
                $langCode = $values->lang_code;

                $langCodeDB = $langCodeRepo->findOneBy(array(
                    'langCode' => $langCode
                ));

                if($langCodeDB){
                    $number = $values->chapter;
                    $timestamp = $values->timestamp;
        
                    $chapterDB = $chapterRepo->findOneBy(array(
                        'langCode' => $langCodeDB,
                        'manga' => $mangaDB,
                        'number' => $number
                    ));

                    if($chapterDB){
                        if($chapterDB->getDate()->getTimestamp() < $timestamp){
                            $chapterDB->setChapterId($chapter_id);
                            $chapterDB->setDate(new \DateTime(date('Y-m-d H:i:s',$timestamp)));
                            $manager->persist($chapterDB);
                            $manager->flush();
                        }
                    } else {
                        $chapter = new Chapter();
                        $chapter->setLangCode($langCodeDB);
                        $chapter->setManga($mangaDB);
                        $chapter->setChapterId($chapter_id);
                        $chapter->setNumber($number);
                        $chapter->setDate(new \DateTime(date('Y-m-d H:i:s',$timestamp)));
                        $manager->persist($chapter);
                        $manager->flush();

                        $output->writeln($mangaDB->getName().' - Langue : ' . $langCodeDB->getLibelle() . ' - Chapitre n°'. $chapter->getNumber() .' ajouté !');
                    }
                }
            }
        }
    }
}
