<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductComponentFormData;

use App\Domain\Product\Repository\ProductComponentRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProductComponentFormDataQueryHandler
{
    public function __construct(
        private ProductComponentRepositoryInterface $componentRepository,
    ) {
    }

    /**
     * @return array{id: string, quantity: int, length: int|null, width: int|null, thickness: int, shapeDescription: string|null}
     */
    public function __invoke(GetProductComponentFormDataQuery $query): array
    {
        $productComponent = $this->componentRepository->getById($query->productComponentId);

        return [
            'id' => $productComponent->getId(),
            'quantity' => $productComponent->getQuantity(),
            'length' => $productComponent->getLength(),
            'width' => $productComponent->getWidth(),
            'thickness' => $productComponent->getThickness(),
            'shapeDescription' => $productComponent->getShapeDescription(),
        ];
    }
}
