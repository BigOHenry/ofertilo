<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Product\Command\CreateProductColorCommand;
use App\Application\Product\Command\EditProductColorCommand;
use App\Domain\Color\Entity\Color;
use App\Domain\Color\Repository\ColorRepositoryInterface;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductColor;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use App\Infrastructure\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ProductApplicationService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private TranslatorInterface $translator,
        private FileUploader $fileUploader,
        private ColorRepositoryInterface $colorRepository,
    ) {
    }

    public function createColorFromCommand(CreateProductColorCommand $command): void
    {
        $color = $command->getColor();
        if ($color === null) {
            throw new \InvalidArgumentException('Color is required');
        }

        $command->getProduct()->addColor($color, $command->getDescription());
        $this->productRepository->save($command->getProduct());
    }

    public function updateColorFromCommand(ProductColor $productColor, EditProductColorCommand $command): void
    {
        $productColor->setColor($command->getColor());
        $productColor->setDescription($command->getDescription());
        $this->productRepository->save($productColor->getProduct());
    }

    public function findById(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    public function findByTypeAndCountry(Type $type, Country $country): ?Product
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

    public function removeColorFromProduct(Product $product, Color $color): void
    {
        $product->removeColor($color);
        $this->productRepository->save($product);
    }

    /**
     * @return array<string, mixed>
     */
    public function getProductColorsData(Product $product): array
    {
        $data = [];
        foreach ($product->getProductColors() as $productColor) {
            $data[] = [
                'id' => $productColor->getId(),
                'color' => $productColor->getColor()->getCode(),
                'color_description' => $productColor->getColor()->getDescription(),
                'description' => $productColor->getDescription(),
                'inStock' => $this->translator->trans($productColor->getColor()->isInStock() ? 'boolean.yes' : 'boolean.no', domain: 'messages'),
            ];
        }

        usort($data, static fn ($a, $b) => $a['color'] <=> $b['color']);

        return [
            'data' => $data,
            'product_id' => $product->getId(),
            'total_colors' => \count($data),
        ];
    }

    /**
     * @return Color[]
     */
    public function getAvailableColorsForProduct(Product $product, ?Color $currentColor = null): array
    {
        $assignedColorIds = [];
        foreach ($product->getProductColors() as $productColor) {
            $colorId = $productColor->getColor()->getId();

            if ($colorId !== null && ($currentColor === null || $colorId !== $currentColor->getId())) {
                $assignedColorIds[] = $colorId;
            }
        }

        return $this->colorRepository->findAvailableColors($assignedColorIds);
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
