<?php

namespace App\Infrastructure\Service;

use Random\RandomException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private readonly string $secureUploadsDirectory,
        private SluggerInterface $slugger
    ) {}

    public function upload(UploadedFile $file, string $entityFolder): array
    {
//        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->guessExtension();

        $randomFilename = $this->generateRandomFilename() . '.' . $extension;

        $targetDirectory = $this->getEntityDirectory($entityFolder);

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetDirectory));
        }

        try {
            $file->move($targetDirectory, $randomFilename);
        } catch (FileException $e) {
            throw new \RuntimeException('Failed to upload file: ' . $e->getMessage());
        }

        return [
            'filename' => $randomFilename,
            'originalName' => $file->getClientOriginalName()
        ];
    }

    /**
     * @throws RandomException
     */
    private function generateRandomFilename(): string
    {
        return bin2hex(random_bytes(6)) . '_' . str_replace('.', '_', uniqid('', true));
    }

    public function getEntityDirectory(string $entityFolder): string
    {
        return $this->secureUploadsDirectory . DIRECTORY_SEPARATOR . $entityFolder;
    }

    public function getFilePath(string $entityFolder, string $filename): string
    {
        return $this->getEntityDirectory($entityFolder) . DIRECTORY_SEPARATOR . $filename;
    }

    public function remove(string $entityFolder, string $filename): bool
    {
        $filePath = $this->getFilePath($entityFolder, $filename);

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }
}
