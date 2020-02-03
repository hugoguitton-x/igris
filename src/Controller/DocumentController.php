<?php

namespace App\Controller;

use App\Entity\Document;
use App\Repository\DocumentRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DocumentController extends AbstractController
{
    /**
     * @Route("/", name="home_page")
     */
    public function homePage(DocumentRepository $repo)
    {
        $documents = $repo->findBy([], ['updatedAt' => 'DESC']);
        return $this->render('document/index.html.twig', [
            'controller_name' => 'DocumentController',
            'documents' => $documents
        ]);
    }
    /**
     * @Route("/document/{id}", name="document_show")
     */
    public function showDocument(Document $document)
    {
        return $this->render('document/show.html.twig', [
            'document' => $document,
        ]);
    }
}
