<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductSizesGrid;

use App\Application\Product\Service\ProductApplicationService;
use App\Domain\Product\Exception\ProductNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProductSizesGridQueryHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    /**
     * @return list<array{id: string, height: int, width: int, thickness: int|null}>
     */
    public function __invoke(GetProductSizesGridQuery $query): array
    {
        $product = $this->productApplicationService->findById($query->getProductId());

        if ($product === null) {
            throw ProductNotFoundException::withId($query->getProductId());
        }

        $data = [];
        foreach ($product->getProductSizes() as $productSize) {
            $data[] = [
                'id' => $productSize->getId(),
                'height' => $productSize->getHeight(),
                'width' => $productSize->getWidth(),
                'thickness' => $productSize->getThickness(),
            ];
        }

        usort($data, static fn ($a, $b) => $a['height'] <=> $b['height']);

        return $data;
    }
}
