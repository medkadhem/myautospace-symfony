<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file, string $subDirectory = ''): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $uploadPath = $this->targetDirectory;
            if ($subDirectory) {
                $uploadPath .= '/' . $subDirectory;
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
            }

            $file->move($uploadPath, $fileName);
        } catch (FileException $e) {
            throw new \RuntimeException('Failed to upload file: ' . $e->getMessage());
        }

        return ($subDirectory ? $subDirectory . '/' : '') . $fileName;
    }

    public function remove(string $filename): void
    {
        $filepath = $this->targetDirectory . '/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
