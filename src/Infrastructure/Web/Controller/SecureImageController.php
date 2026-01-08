<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Domain\Shared\File\Repository\FileRepositoryInterface;
use App\Domain\User\ValueObject\Role;
use App\Infrastructure\Service\FileStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SecureImageController extends AbstractController
{
    /**
     * New route - using File ID.
     */
    #[Route('/secure/file/{id}', name: 'secure_file', requirements: ['id' => '[0-9a-f-]+'])]
    #[IsGranted(Role::READER->value)]
    public function showFile(
        string $id,
        FileRepositoryInterface $fileRepository,
        FileStorage $fileStorage,
    ): Response {
        $file = $fileRepository->findById($id);

        if (!$file) {
            throw $this->createNotFoundException('File not found');
        }

        $filePath = $fileStorage->getPath($file);

        if (!$fileStorage->exists($file)) {
            throw $this->createNotFoundException('File not found on filesystem');
        }

        $response = new BinaryFileResponse($filePath);

        // Set proper headers
        $response->headers->set('Content-Type', $file->getMimeType());
        $response->headers->set('Content-Disposition', \sprintf(
            'inline; filename="%s"',
            $file->getOriginalName()
        ));

        return $response;
    }

    /**
     * Legacy route - for backward compatibility with old encoded filenames.
     *
     * @deprecated Use showFile() with File ID instead
     */
    #[Route('/secure/image/{entityFolder}/{filename}', name: 'secure_image', requirements: ['filename' => '.+'])]
    #[IsGranted(Role::READER->value)]
    public function showImage(
        string $entityFolder,
        string $filename,
        FileStorage $fileStorage,
    ): Response {
        $filename = base64_decode($filename, true);
        if ($filename === false) {
            throw $this->createNotFoundException('Image not found');
        }

        // Try to find file by filename in repository
        // This is legacy support - you might want to remove this eventually
        $imagePath = $fileStorage->getEntityDirectory($entityFolder) . \DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($imagePath)) {
            throw $this->createNotFoundException('Image not found');
        }

        return new BinaryFileResponse($imagePath);
    }
}
