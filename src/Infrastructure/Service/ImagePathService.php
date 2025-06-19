<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

readonly class ImagePathService
{
    public function __construct(
        private string $productsImagesDirectory,
    ) {
    }

    public function getProductImagePath(?string $filename): ?string
    {
        if (!$filename) {
            return null;
        }

        return $this->productsImagesDirectory . '/' . $filename;
    }

    public function getProductsDirectory(): string
    {
        return $this->productsImagesDirectory;
    }
}
