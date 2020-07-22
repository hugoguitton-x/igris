<?php

namespace App\Controller;

use App\Entity\Manga;
use App\Entity\Chapter;
use App\Form\MangaType;
use App\Entity\LanguageCode;
use App\Repository\MangaRepository;
use App\Repository\ChapterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MangaController extends AbstractController
{
    /**
     * @Route("/manga", name="manga")
     */
    public function index(ChapterRepository $repo, PaginatorInterface $paginator, Request $request)
    {      
        $query = $repo->findLastChapterOrderByDateQuery();

        $chapters = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            16
        );

        return $this->render('manga/index.html.twig', [
            'controller_name' => 'MangaController',
            'chapters' => $chapters
        ]);
    }

    /**
     * @Route("/admin/manga/new", name="manga_new")
     * @Route("/admin/manga/edit/{id}", name="manga_edit")
     */
    public function form(
        Request $request,
        EntityManagerInterface $manager,
        Manga $manga = null
    ) {
        if (!$manga) {
            $manga = new Manga();
        }

        $form = $this->createForm(MangaType::class, $manga);
        $form->handleRequest($request);

        if($manga->getId() !== null){
            $edit = true;
        } else {
            $edit = false;
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $manga =  $this->loadMangaFromMangadexApi($form->get('manga_id')->getData(), $manager);

            if($edit){
                $this->addFlash('success', $manga->getName().' modifié avec succès');
            } else {
                $this->addFlash('success', $manga->getName().' ajouté avec succès');
            }
            
            return $this->redirectToRoute('manga');
        }

        return $this->render('manga/form.html.twig', [
            'formManga' => $form->createView(),
            'editMode' => $edit
        ]);
    }

    /**
     * @Route("/manga/list", name="manga_list")
     */
    public function listeManga(MangaRepository $repo, PaginatorInterface $paginator, Request $request)
    {      
        $query = $repo->findMangaOrderByNameQuery();

        $mangas = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            24
        );

        return $this->render('manga/liste.html.twig', [
            'controller_name' => 'MangaController',
            'mangas' => $mangas
        ]);
    }

    /**
     * @Route("/manga/{language}", name="manga_language")
     */
    public function indexFr(ChapterRepository $repo, EntityManagerInterface $manager, PaginatorInterface $paginator, Request $request, string $language)
    {      
        $array_language = [
            'en' => 'English',
            'fr' => 'French'
        ];
        $query = $repo->findLastChapterOrderByDateQuery($array_language[$language]);

        $chapters = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            16
        );

        return $this->render('manga/index.html.twig', [
            'controller_name' => 'MangaController',
            'chapters' => $chapters
        ]);
    }

    private function loadMangaFromMangadexApi(int $mangaId, EntityManagerInterface $manager){
        $mangaRepo = $manager->getRepository(Manga::class);
        $langCodeRepo = $manager->getRepository(LanguageCode::class);
        $chapterRepo = $manager->getRepository(Chapter::class);

        $mangadexURL = $this->getParameter('mangadex_url');

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
            $mangaDB->setName($manga->title);

            $imageFile = file_get_contents($urlImage);
            $file = $this->getParameter('kernel.project_dir') . "/public/uploads/mangas/".$info['basename'];
            file_put_contents($file, $imageFile);

            $mangaDB->setImage($image);
            $mangaDB->setMangaId($mangaId);
            $manager->persist($mangaDB);
            $manager->flush();
        } else {
            if(!file_exists($this->getParameter('kernel.project_dir') . "/public/uploads/mangas/".$info['basename'])){

                $imageFile = file_get_contents($urlImage);
                $file = $this->getParameter('kernel.project_dir') . "/public/uploads/mangas/".$info['basename'];
                file_put_contents($file, $imageFile);

                $mangaDB->setImage($image);
            } else if($mangaDB->getImage() != $image){
                $imageFile = file_get_contents($urlImage);
                $file = $this->getParameter('kernel.project_dir') . "/public/uploads/mangas/".$info['basename'];
                file_put_contents($file, $imageFile);

                $mangaDB->setImage($image);
            } else if(md5(file_get_contents($urlImage)) != md5(file_get_contents($this->getParameter('kernel.project_dir') . "/public/uploads/mangas/".$info['basename']))) {
                $imageFile = file_get_contents($urlImage);
                $file = $this->getParameter('kernel.project_dir') . "/public/uploads/mangas/".$info['basename'];
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
                    }
                }
            }
        }

        return $mangaDB;
    }
}
