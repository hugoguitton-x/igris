<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileRemover
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function removeImage(Filesystem $file, string $folder, string $fileName): bool
    {
        try {
            $file->remove($this->getTargetDirectory() . $folder . '/' . $fileName);
        } catch (FileException $e) {
            throw $e;
        }

        return true;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
