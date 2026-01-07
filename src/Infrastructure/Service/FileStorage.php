<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Shared\File\Entity\File;
use App\Domain\Shared\File\Exception\FileErrorException;
use Random\RandomException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileStorage
{
    public function __construct(
        private string $secureUploadsDirectory,
    ) {
    }

    public function store(File $file, UploadedFile $uploadedFile): void
    {
        $targetDirectory = $this->secureUploadsDirectory . DIRECTORY_SEPARATOR . File::STORAGE_FOLDER;

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw FileErrorException::failToCreateOrFindFolder($targetDirectory);
        }

        try {
            $uploadedFile->move($targetDirectory, $file->getFilename());
        } catch (FileException $e) {
            throw FileErrorException::failToUpload();
        }
    }

    public function delete(File $file): void
    {
        $path = $this->getPath($file);

        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function getPath(File $file): string
    {
        return $this->secureUploadsDirectory
            . DIRECTORY_SEPARATOR
            . File::STORAGE_FOLDER
            . DIRECTORY_SEPARATOR
            . $file->getFilename();
    }

    public function exists(File $file): bool
    {
        return file_exists($this->getPath($file));
    }

    public function getUrl(File $file): string
    {
        return '/' . File::STORAGE_FOLDER . '/' . $file->getFilename();
    }

    /**
     * @deprecated Use File::STORAGE_FOLDER constant instead
     */
    public function getEntityDirectory(string $entityFolder): string
    {
        return $this->secureUploadsDirectory . DIRECTORY_SEPARATOR . $entityFolder;
    }
}
