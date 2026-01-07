<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductColorFormData;

use App\Application\Product\Service\ProductApplicationService;
use App\Domain\Color\Entity\Color;
use App\Domain\Product\Entity\Product;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProductColorFormDataQueryHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    /**
     * @return array{id: string, product: Product, color: Color, description: string|null}
     */
    public function __invoke(GetProductColorFormDataQuery $query): array
    {
        $product = $this->productApplicationService->getById($query->productId);
        $productColor = $product->getProductColorById($query->productColorId);

        return [
            'id' => $productColor->getId(),
            'product' => $product,
            'color' => $productColor->getColor(),
            'description' => $productColor->getDescription(),
        ];
    }
}
