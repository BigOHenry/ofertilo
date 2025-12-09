<?php

declare(strict_types=1);

namespace App\Application\Query\Product\GetProductColorFormData;

use App\Application\Service\ProductApplicationService;
use App\Domain\Color\Entity\Color;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Exception\ProductColorNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProductColorFormDataQueryHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    /**
     * @return array{id: int|null, product: Product, color: Color, description: string|null}
     */
    public function __invoke(GetProductColorFormDataQuery $query): array
    {
        $productColor = $this->productApplicationService->findProductColorById($query->getId());

        if ($productColor === null) {
            throw ProductColorNotFoundException::withId($query->getId());
        }

        return [
            'id' => $productColor->getId(),
            'product' => $productColor->getProduct(),
            'color' => $productColor->getColor(),
            'description' => $productColor->getDescription(),
        ];
    }
}
