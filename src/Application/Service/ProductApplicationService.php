<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Exception\ProductNotFoundException;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Shared\Entity\Country;
use App\Infrastructure\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class ProductApplicationService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private FileUploader $fileUploader,
    ) {
    }

    public function findById(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    /**
     * @throws ProductNotFoundException
     */
    public function getById(int $id): Product
    {
        return $this->productRepository->getById($id);
    }

    public function findByTypeAndCountry(ProductType $type, Country $country): ?Product
    {
        return $this->productRepository->findByTypeAndCountry($type, $country);
    }

    public function save(Product $product): void
    {
        $this->productRepository->save($product);
    }

    public function delete(Product $product): void
    {
        $this->productRepository->remove($product);
    }

    public function handleImageUpload(Product $product, UploadedFile $imageFile): void
    {
        try {
            $uploadResult = $this->fileUploader->upload($imageFile, $product->getEntityFolder());
            $product->setImageFilename($uploadResult['filename']);
            $product->setImageOriginalName($uploadResult['originalName']);
        } catch (\RuntimeException $e) {
            // TODO throw exception
        }
    }

    public function removeProductImage(Product $product, string $filename): void
    {
        try {
            $this->fileUploader->remove($product->getEntityFolder(), $filename);
        } catch (\Exception $e) {
            // TODO Log error but don't fail the operation
        }
    }
}
