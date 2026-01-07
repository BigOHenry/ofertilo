<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductSizeFormData;

use App\Application\Product\Service\ProductApplicationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProductSizeFormDataQueryHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    /**
     * @return array{id: string, height: int, width: int, thickness: int|null}
     */
    public function __invoke(GetProductSizeFormDataQuery $query): array
    {
        $product = $this->productApplicationService->getById($query->productId);
        $productSize = $product->getProductSizeById($query->productSizeId);

        return [
            'id' => $productSize->getId(),
            'height' => $productSize->getHeight(),
            'width' => $productSize->getWidth(),
            'thickness' => $productSize->getThickness(),
        ];
    }
}
