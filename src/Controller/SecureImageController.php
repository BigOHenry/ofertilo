<?php

namespace App\Controller;

use App\Domain\User\ValueObject\Role;
use App\Infrastructure\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SecureImageController extends AbstractController
{
    #[Route('/secure/image/{entityFolder}/{filename}', name: 'secure_image', requirements: ['filename' => '.+'])]
    #[IsGranted(Role::READER->value)]
    public function showImage(
        string $entityFolder,
        string $filename,
        FileUploader $fileUploader
    ): Response {
        $filename = base64_decode($filename);
        if ($filename === false) {
            throw $this->createNotFoundException('Image not found');
        }
        $imagePath = $fileUploader->getFilePath($entityFolder, $filename);

        if (!file_exists($imagePath)) {
            throw $this->createNotFoundException('Image not found');
        }

        return new BinaryFileResponse($imagePath);
    }
}
