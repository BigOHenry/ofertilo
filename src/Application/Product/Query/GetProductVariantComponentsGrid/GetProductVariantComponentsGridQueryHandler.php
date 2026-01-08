<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductVariantComponentsGrid;

use App\Application\Product\Service\ProductApplicationService;
use App\Domain\Product\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class GetProductVariantComponentsGridQueryHandler
{
    public function __construct(
        private ProductVariantRepositoryInterface $variantRepository,
    ) {
    }

    /**
     * @return list<array{id: string, length: int, width: int, thickness: int, atypicalShape: bool}>
     */
    public function __invoke(GetProductVariantComponentsGridQuery $query): array
    {
        $productVariant = $this->variantRepository->getById($query->productVariantId);

        $data = [];
        foreach ($productVariant->getProductComponents() as $productComponent) {
            $data[] = [
                'id' => $productComponent->getId(),
                'length' => $productComponent->getLength(),
                'width' => $productComponent->getWidth(),
                'thickness' => $productComponent->getThickness(),
                'atypicalShape' => $productComponent->getShapeDescription() !== null || $productComponent->getBlueprintFile() !== null
            ];
        }

        usort($data, static fn ($a, $b) => $a['length'] <=> $b['length']);

        return $data;
    }
}
