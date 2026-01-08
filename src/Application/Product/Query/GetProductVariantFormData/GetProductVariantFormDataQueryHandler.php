<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductVariantFormData;

use App\Application\Product\Service\ProductApplicationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProductVariantFormDataQueryHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    /**
     * @return array{id: string, height: int, width: int, thickness: int|null}
     */
    public function __invoke(GetProductVariantFormDataQuery $query): array
    {
        $product = $this->productApplicationService->getById($query->productId);
        $productVariant = $product->getProductVariantById($query->productVariantId);

        return [
            'id' => $productVariant->getId(),
            'height' => $productVariant->getHeight(),
            'width' => $productVariant->getWidth(),
            'thickness' => $productVariant->getThickness(),
        ];
    }
}
