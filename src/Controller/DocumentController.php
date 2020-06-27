<?php

namespace App\Controller;

use App\Entity\Document;
use App\Form\DocumentType;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DocumentController extends AbstractController
{

    /**
     * @Route("/", name="home_page")
     */
    public function homePageTemp() {
        return $this->render('site/home_page.html.twig', [
            'controller_name' => 'DocumentController',
        ]);
    }

    /**
     * @Route("/document", name="document")
     */
    public function index(
        DocumentRepository $repo,
        PaginatorInterface $paginator,
        Request $request
    ) {
        $query = $repo->findByUtilisateurQuery($this->getUser());

        $documents = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            4
        );
        return $this->render('document/index.html.twig', [
            'controller_name' => 'DocumentController',
            'documents' => $documents
        ]);
    }

    /**
     * @Route("/document/new", name="document_new")
     * @Route("/document/edit/{id}", name="document_edit")
     */
    public function form(
        Request $request,
        EntityManagerInterface $manager,
        Document $document = null
    ) {
        if (!$document) {
            $document = new Document();
            $document->setCreatedAt(new \DateTime());
            $document->setAuteur($this->getUser());
        }

        $form = $this->createForm(DocumentType::class, $document);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $document->setUpdatedAt(new \DateTime());
            $manager->persist($document);
            $manager->flush();

            return $this->redirectToRoute('document');
        }

        return $this->render('document/form.html.twig', [
            'formDocument' => $form->createView(),
            'editMode' => $document->getId() !== null
        ]);
    }

    /**
     * @Route("/document/{id}", name="document_show")
     */
    public function showDocument(Document $document)
    {
        return $this->render('document/show.html.twig', [
            'document' => $document
        ]);
    }
}
