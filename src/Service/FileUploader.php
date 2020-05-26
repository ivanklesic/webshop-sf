<?php


namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use \Transliterator;

class FileUploader
{
    private $targetDirectory;
    private $transliterator;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
        $this->transliterator = Transliterator::create('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()');
    }

    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->transliterator->transliterate($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {

        }

        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

}