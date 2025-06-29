<?php

declare(strict_types=1);

namespace App\Application\Product;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductColor;
use App\Domain\Product\Exception\ProductAlreadyExistsException;
use App\Domain\Product\Factory\ProductFactory;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use App\Domain\Translation\Repository\TranslationLoaderInterface;
use App\Infrastructure\Service\FileUploader;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ProductFactory $productFactory,
        private TranslationLoaderInterface $translationLoader,
        private TranslatorInterface $translator,
        private FileUploader $fileUploader,
    ) {
    }

    public function createEmpty(): Product
    {
        return $this->productFactory->createEmpty();
    }

    public function create(Type $type, Country $country): Product
    {
        if ($this->productRepository->findByTypeAndCountry($type, $country)) {
            throw ProductAlreadyExistsException::withTypeAndCountry($type, $country);
        }

        return $this->productFactory->create($type, $country);
    }

    public function save(Product $product): void
    {
        $this->productRepository->save($product);
    }

    public function delete(Product $product): void
    {
        $this->productRepository->remove($product);
    }

    public function createWithImage(Product $product, ?UploadedFile $imageFile = null): void
    {
        if ($imageFile) {
            $this->handleImageUpload($product, $imageFile);
        }

        $this->productRepository->save($product);
    }

    public function updateWithImage(Product $product, ?UploadedFile $imageFile = null): void
    {
        if ($imageFile) {
            $oldImageFilename = $product->getImageFilename();
            if ($oldImageFilename) {
                $this->removeProductImage($product, $oldImageFilename);
            }

            $this->handleImageUpload($product, $imageFile);
        }

        $this->productRepository->save($product);
    }

    public function deleteWithImage(Product $product): void
    {
        $imageFilename = $product->getImageFilename();
        if ($imageFilename) {
            $this->removeProductImage($product, $imageFilename);
        }

        $this->productRepository->remove($product);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPaginatedProducts(Request $request): array
    {
        $page = max((int) $request->query->get('page', 1), 1);
        $size = min((int) $request->query->get('size', 10), 100);
        $offset = ($page - 1) * $size;

        $qb = $this->productRepository->createQueryBuilder('m')
                                       ->setFirstResult($offset)
                                       ->setMaxResults($size)
        ;

        $sortData = $request->query->all('sort');
        $sortField = $sortData['field'] ?? null;
        $sortDir = $sortData['dir'] ?? 'asc';

        $allowedFields = ['name', 'type'];
        $allowedDirections = ['asc', 'desc'];

        if (
            \in_array($sortField, $allowedFields, true)
            && \in_array(mb_strtolower($sortDir), $allowedDirections, true)
        ) {
            $qb->orderBy("m.$sortField", mb_strtoupper($sortDir));
        }

        $paginator = new Paginator($qb);
        $total = \count($paginator);

        $data = [];
        /** @var Product $product */
        foreach ($paginator as $product) {
            $this->translationLoader->loadTranslations($product);
            $data[] = [
                'id' => $product->getId(),
                'description' => $product->getDescription($request->getLocale()),
                'country' => '(' . $product->getCountry()->getAlpha2() . ') ' . $product->getCountry()->getName(),
                'type' => $this->translator->trans('product.type.' . $product->getType()->value, domain: 'enum'),
                'enabled' => $this->translator->trans($product->isEnabled() ? 'boolean.yes' : 'boolean.no', domain: 'messages'),
            ];
        }

        return [
            'data' => $data,
            'last_page' => ceil($total / $size),
            'total' => $total,
        ];
    }

    public function addColorToProduct(Product $product, Color $color, ?string $description = null): void
    {
        $product->addColor($color, $description);
        $this->productRepository->save($product);
    }

    public function updateProductColor(ProductColor $productColor, Color $color, ?string $description = null): void
    {
        $productColor->setColor($color);
        $productColor->setDescription($description);
        $this->productRepository->save($productColor->getProduct());
    }

    public function removeColorFromProduct(Product $product, Color $color): void
    {
        $product->removeColor($color);
        $this->productRepository->save($product);
    }

    public function createEmptyColor(Product $product): ProductColor
    {
        return ProductColor::createEmpty($product);
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
                'in_stock' => $this->translator->trans($productColor->getColor()->isInStock() ? 'boolean.yes' : 'boolean.no', domain: 'messages'),
            ];
        }

        usort($data, static fn ($a, $b) => $a['color'] <=> $b['color']);

        return [
            'data' => $data,
            'product_id' => $product->getId(),
            'total_colors' => \count($data),
        ];
    }

    private function handleImageUpload(Product $product, UploadedFile $imageFile): void
    {
        try {
            $uploadResult = $this->fileUploader->upload($imageFile, $product->getEntityFolder());
            $product->setImageFilename($uploadResult['filename']);
            $product->setImageOriginalName($uploadResult['originalName']);
        } catch (\RuntimeException $e) {
            // TODO throw exception
        }
    }

    private function removeProductImage(Product $product, string $filename): void
    {
        try {
            $this->fileUploader->remove($product->getEntityFolder(), $filename);
        } catch (\Exception $e) {
            // TODO Log error but don't fail the operation
        }
    }
}
