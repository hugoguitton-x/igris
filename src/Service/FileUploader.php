<?php

namespace App\Service;

use Twig\Environment;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploader
{
    private $targetDirectory;
    private $slugger;

    public function __construct($targetDirectory, SluggerInterface $slugger, Environment $twig)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
        $this->twig = $twig;
    }

    public function uploadImage(UploadedFile $file, string $folder): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        try {
            $file->move($this->getTargetDirectory().$folder.'/', $fileName);
        } catch (FileException $e) {
            return $this->twig->render('errors/file_error.html.twig', [
                'message' => $e->getMessage(),
                'target' => $this->getTargetDirectory().$folder.'/',
            ]);
        }

        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}