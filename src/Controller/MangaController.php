<?php

namespace App\Controller;

use App\Entity\Manga;
use App\Form\MangaType;
use App\Entity\Language;
use App\Entity\LastChapter;
use App\Service\FileUploader;
use App\Repository\MangaRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LastChapterRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MangaController extends AbstractController
{
    /**
     * @Route("/manga", name="manga")
     */
    public function index(LastChapterRepository $repo, MangaRepository $mangaRepo, EntityManagerInterface $manager, PaginatorInterface $paginator, Request $request)
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
        Manga $manga = null,
        FileUploader $fileUploader
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

        if ($form->isSubmitted() && $form->isValid() && filter_var($manga->getRss(), FILTER_VALIDATE_URL) !== false) {
            if(strpos($manga->getRss(),'mangadex.') !== false && strpos($manga->getRss(),'/rss/') !== false){
                $imageFile = $form->get('image')->getData();
                if ($imageFile) {
                    $imageFile = $fileUploader->uploadImage($imageFile, 'mangas');
                } else {
                    $imageFile = '';
                }
    
                $manga =  $this->manageRss($manga->getRss(), $manager, $imageFile);
            }

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
    public function indexFr(LastChapterRepository $repo, MangaRepository $mangaRepo, EntityManagerInterface $manager, PaginatorInterface $paginator, Request $request, string $language)
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


    private function manageRss(string $rss, EntityManagerInterface $manager, string $imageFile = null)
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
                    $mangaArray['name'] = substr($item->getTitle(), 0, strpos($item->getTitle(), 'Chapter') - 3);
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
            $manga->setImage($imageFile);
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
            $manager->flush();

            return $manga;
        }
    }
}
