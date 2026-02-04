<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductComponentsGrid;

use App\Domain\Product\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProductComponentsGridQueryHandler
{
    public function __construct(
        private ProductVariantRepositoryInterface $variantRepository,
    ) {
    }

    /**
     * @return list<array{id: string, quantity: int, dimensions: string, atypicalShape: bool}>
     */
    public function __invoke(GetProductComponentsGridQuery $query): array
    {
        $productVariant = $this->variantRepository->getById($query->productVariantId);

        $data = [];
        foreach ($productVariant->getProductComponents() as $productComponent) {
            $data[] = [
                'id' => $productComponent->getId(),
                'quantity' => $productComponent->getQuantity(),
                'dimensions' => $productComponent->getDimensions(),
                'atypicalShape' => $productComponent->getShapeDescription() !== null || $productComponent->getBlueprintFile() !== null,
            ];
        }

        usort($data, static fn ($a, $b) => $a['dimensions'] <=> $b['dimensions']);

        return $data;
    }
}
