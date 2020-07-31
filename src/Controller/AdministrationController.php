<?php

namespace App\Controller;

use App\Entity\Manga;
use App\Entity\Serie;
use App\Entity\Chapter;
use App\Form\MangaType;
use App\Form\SerieType;
use App\Form\EditUserType;
use App\Entity\Utilisateur;
use App\Entity\LanguageCode;
use App\Service\FileRemover;
use App\Service\FileUploader;
use Abraham\TwitterOAuth\TwitterOAuth;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin", name="admin_")
 */
class AdministrationController extends AbstractController
{
    /**
     * @Route("", name="index")
     */
    public function index()
    {
        return $this->render('administration/index.html.twig', [
            'controller_name' => 'AdministrationController',
        ]);
    }

    /**
     * @Route("/user", name="user")
     */
    public function user(Request $request, UtilisateurRepository $repo, PaginatorInterface $paginator)
    {
        $query = $repo->getUtilisateurAdminInfosOrderByUsernameQuery();

        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('administration/user.html.twig', [
            'controller_name' => 'AdministrationController',
            'users' => $users
        ]);
    }

    /**
     * @Route("/user/edit/{id}", name="edit_user")
     */
    public function editUser(Utilisateur $user, Request $request, EntityManagerInterface $manager, FileUploader $fileUploader, FileRemover $fileRemover, TranslatorInterface $translator)
    {
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('avatar')->getData();
            if ($imageFile) {
                $old_avatar = $user->getAvatar();
                $imageFile = $fileUploader->uploadImage($imageFile, 'avatar');
                $user->setAvatar($imageFile);
                $fileRemover->removeImage(new Filesystem(), 'avatar', $old_avatar);
            }

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', $translator->trans('successfully.modified', ['%slug%' => ucfirst($user->getUsername())]));
            return $this->redirectToRoute('admin_user');
        }
        
        return $this->render('administration/edituser.html.twig', [
            'formUser' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/delete/{id}", name="delete_user")
     */
    public function deleteUser(Utilisateur $user, Request $request, EntityManagerInterface $manager, FileRemover $fileRemover, TranslatorInterface $translator)
    {
        $avatar = $user->getAvatar();

        if($avatar !== 'default.png'){
            $fileRemover->removeImage(new Filesystem(), 'avatar', $avatar);
        }

        $manager->remove($user);
        $manager->flush();

        $this->addFlash('warning', $translator->trans('successfully.deleted', ['%slug%' => ucfirst($user->getUsername())]));
        return $this->redirectToRoute('admin_user');
    }

    /**
     * @Route("/serie/new", name="serie_new")
     * @Route("/serie/edit/{id}", name="serie_edit")
     */
    public function manageFormSerie(
        Request $request,
        EntityManagerInterface $manager,
        Serie $serie = null,
        FileUploader $fileUploader,
        TranslatorInterface $translator
    ) {
        if (!$serie) {
            $serie = new Serie();
            $serie->setNoteMoyenne(0);
        }

        $form = $this->createForm(SerieType::class, $serie);
        $form->handleRequest($request);

        if($serie->getId() !== null){
            $edit = true;
        } else {
            $edit = false;
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageFile = $fileUploader->uploadImage($imageFile, 'series');
            } else {
                $imageFile = '';
            }

            $serie->setSynopsis(nl2br($serie->getSynopsis()));
            $serie->setCreatedAt(new \DateTime());
            $serie->setImage($imageFile);

            $manager->persist($serie);
            $manager->flush();

            if($edit){
                $this->addFlash('success', $translator->trans('successfully.modified', ['%slug%' => ucfirst($serie->getNom())]));
            } else {
                $this->addFlash('success', $translator->trans('successfully.added', ['%slug%' => ucfirst($serie->getNom())]));
            }

            return $this->redirectToRoute('serie_index');
        }

        return $this->render('serie/form.html.twig', [
            'formSerie' => $form->createView(),
            'editMode' => $edit,
        ]);
    }

    /**
     * @Route("/manga/new", name="manga_new")
     * @Route("/manga/edit/{id}", name="manga_edit")
     */
    public function manageFormManga(
        Request $request,
        EntityManagerInterface $manager,
        Manga $manga = null,
        TranslatorInterface $translator
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

            $manga =  $this->loadMangaFromMangadexApi($form->get('manga_id')->getData(), $manager, $translator);

            return $this->redirectToRoute('manga_index');
        }

        return $this->render('manga/form.html.twig', [
            'formManga' => $form->createView(),
            'editMode' => $edit
        ]);
    }


    private function loadMangaFromMangadexApi(int $mangaId, EntityManagerInterface $manager, TranslatorInterface $translator){
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
            $mangaDB->setName(html_entity_decode($manga->title, ENT_QUOTES, 'UTF-8'));

            $imageFile = file_get_contents($urlImage);
            $file = $this->getParameter('kernel.project_dir') . "/public/uploads/mangas/".$info['basename'];
            file_put_contents($file, $imageFile);

            $mangaDB->setImage($image);
            $mangaDB->setMangaId($mangaId);
            $manager->persist($mangaDB);
            $manager->flush();

            $string = '"'.$mangaDB->getName().'"' .' a été ajouté !' . PHP_EOL;
            $string .= 'Disponible ici : ' . $mangadexURL . '/manga/' . $mangaId;
            $this->postTweetMangaAdd($string, array($file));
            $this->addFlash('success', $translator->trans('successfully.added', ['%slug%' => ucfirst($mangaDB->getName())]));

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
            $this->addFlash('warning', $translator->trans('successfully.modified', ['%slug%' => ucfirst($mangaDB->getName())]));
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


    private function postTweetMangaAdd($str = '', $mediaArray = null)
    {
        $consumerKey = $this->getParameter('consumer_key');
        $consumerSecret = $this->getParameter('consumer_secret');
        $oauthToken = $this->getParameter('oauth_token');
        $oauthTokenSecret = $this->getParameter('oauth_token_secret');


        if(!empty($consumerKey) && !empty($consumerSecret) && !empty($consumerKey) && !empty($oauthTokenSecret)) {
            $connection =  new TwitterOAuth($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret);

            if (is_array($mediaArray)) {
    
                $mediaIDS = array();
    
                foreach ($mediaArray as $key => $media_path) {
                    $mediaOBJ = $connection->upload('media/upload', ['media' => $media_path]);
                    array_push($mediaIDS, $mediaOBJ->media_id_string);
                }
    
                $mediaIDstr = implode(',', $mediaIDS);
            }
    
            $arrayCfg['status'] = $str;
            $arrayCfg['media_ids'] = $mediaIDstr;
    
            $statuses = $connection->post("statuses/update", $arrayCfg);
    
            return $statuses;
        }
        
        return 0;
    }
}
