<?php

declare(strict_types=1);

namespace App\Application\Product\Service;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Exception\ProductNotFoundException;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Shared\Country\Entity\Country;
use App\Infrastructure\Service\FileStorage;

final readonly class ProductApplicationService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private FileStorage $fileStorage,
    ) {
    }

    public function findById(string $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    /**
     * @throws ProductNotFoundException
     */
    public function getById(string $id): Product
    {
        return $this->productRepository->getById($id);
    }

    public function findByTypeAndCountry(ProductType $type, Country $country): ?Product
    {
        return $this->productRepository->findByTypeAndCountry($type, $country);
    }

    public function save(Product $product): void
    {
        $imageFile = $product->getImageFile();
        if ($imageFile && $imageFile->getUploadedFile()) {
            $this->fileStorage->store($imageFile, $imageFile->getUploadedFile());
        }

        $this->productRepository->save($product);
    }

    public function delete(Product $product): void
    {
        $imageFile = $product->getImageFile();
        if ($imageFile && $this->fileStorage->exists($imageFile)) {
            $this->fileStorage->delete($imageFile);
        }

        $this->productRepository->remove($product);
    }
}
