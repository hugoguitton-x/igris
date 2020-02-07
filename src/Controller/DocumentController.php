<?php

namespace App\Controller;

use App\Entity\Document;
use App\Repository\DocumentRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DocumentController extends AbstractController
{
    /**
     * @Route("/", name="home_page")
     */
    public function homePage(
        DocumentRepository $repo,
        PaginatorInterface $paginator,
        Request $request
    ) {
        $request->query->replace([
            'filterField' => $request->get('filterField'),
            'filterValue' => '*' . $request->get('filterValue') . '*'
        ]);

        $queryBuilder = $repo
            ->createQueryBuilder('d')
            ->orderBy('d.updatedAt', 'DESC');

        $query = $queryBuilder->getQuery();

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
     * @Route("/document/{id}", name="document_show")
     */
    public function showDocument(Document $document)
    {
        return $this->render('document/show.html.twig', [
            'document' => $document
        ]);
    }
}
