<?php

namespace App\Controller;

use App\Entity\Manga;
use App\Entity\Serie;
use App\Form\MangaType;
use App\Form\SerieType;
use App\Form\EditUserType;
use App\Entity\Utilisateur;
use App\Service\FileRemover;
use Psr\Log\LoggerInterface;
use App\Helper\TwitterHelper;
use App\Service\FileUploader;
use App\Repository\MangaRepository;
use App\Helper\MangaMangadexApiHelperV5;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @Route("/admin", name="admin_")
 */
class AdministrationController extends AbstractController
{

    private $logger;
    private $twitter;

    public function __construct(LoggerInterface $logger,  ParameterBagInterface $params)
    {
        $this->logger = $logger;
        $this->twitter = new TwitterHelper($params);
    }

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

        $users->setCustomParameters([
            'align' => 'center', # center|right
            'size' => 'small', # small|large
        ]);

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

            $this->addFlash('success', $translator->trans('successfully.modified', ['%slug%' => ucfirst($user->getUserIdentifier())]));
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

        if ($avatar !== 'default.png') {
            $fileRemover->removeImage(new Filesystem(), 'avatar', $avatar);
        }

        $manager->remove($user);
        $manager->flush();

        $this->addFlash('warning', $translator->trans('successfully.deleted', ['%slug%' => ucfirst($user->getUserIdentifier())]));
        return $this->redirectToRoute('admin_user');
    }

    /**
     * @Route("/serie/new", name="serie_new")
     * @Route("/serie/edit/{id}", name="serie_edit")
     */
    public function manageFormSerie(
        Request $request,
        EntityManagerInterface $manager,
        FileUploader $fileUploader,
        TranslatorInterface $translator,
        Serie $serie = null
    ) {
        if (!$serie) {
            $serie = new Serie();
            $serie->setNoteMoyenne(0);
        }

        $form = $this->createForm(SerieType::class, $serie);
        $form->handleRequest($request);

        if ($serie->getId() !== null) {
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

            if ($edit) {
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
        MangaRepository $repo,
        EntityManagerInterface $manager,
        TranslatorInterface $translator,
        Manga $manga = null
    ) {
        if (!$manga) {
            $manga = new Manga();
        }

        $form = $this->createForm(MangaType::class, $manga);
        $form->handleRequest($request);

        if ($manga->getId() !== null) {
            $edit = true;
        } else {
            $edit = false;
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $mangaMangadexApi = new MangaMangadexApiHelperV5($this->container->get('parameter_bag'), $manager);
            $result = $mangaMangadexApi->refreshMangaById($form->get('mangadex_id')->getData());

            if ($result === "created") {
                $manga = $repo->findOneBy(["mangadex_id" => $form->get('mangadex_id')->getData()]);
                $this->addFlash('success',  $translator->trans('successfully.added', ['%slug%' => ucfirst($manga->getName())]));
            } else if ($result === "updated") {
                $this->addFlash('warning',  $translator->trans('successfully.modified', ['%slug%' => ucfirst($manga->getName())]));
            }

            return $this->redirectToRoute('manga_list');
        }

        return $this->render('manga/form.html.twig', [
            'formManga' => $form->createView(),
            'editMode' => $edit
        ]);
    }
}
