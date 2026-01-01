<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductColorsGrid;

use App\Application\Product\Service\ProductApplicationService;
use App\Domain\Product\Exception\ProductNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProductColorsGridQueryHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    /**
     * @return list<array{id: int|null, color: int, colorDescription: string|null, description: string|null, inStock: bool}>
     */
    public function __invoke(GetProductColorsGridQuery $query): array
    {
        $product = $this->productApplicationService->findById($query->getProductId());

        if ($product === null) {
            throw ProductNotFoundException::withId($query->getProductId());
        }

        $data = [];
        foreach ($product->getProductColors() as $productColor) {
            $data[] = [
                'id' => $productColor->getId(),
                'color' => $productColor->getColor()->getCode(),
                'colorDescription' => $productColor->getColor()->getDescription(),
                'description' => $productColor->getDescription(),
                'inStock' => $productColor->getColor()->isInStock(),
            ];
        }

        usort($data, static fn ($a, $b) => $a['color'] <=> $b['color']);

        return $data;
    }
}
