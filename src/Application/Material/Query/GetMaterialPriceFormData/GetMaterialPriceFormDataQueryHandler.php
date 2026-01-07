<?php

declare(strict_types=1);

namespace App\Application\Material\Query\GetMaterialPriceFormData;

use App\Application\Material\Service\MaterialApplicationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetMaterialPriceFormDataQueryHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    /**
     * @return array{id: string, wood: string, thickness: int, price: string}
     */
    public function __invoke(GetMaterialPriceFormDataQuery $query): array
    {
        $material = $this->materialApplicationService->getById($query->materialId);
        $materialPrice = $material->getPriceById($query->priceId);

        return [
            'id' => $materialPrice->getId(),
            'wood' => $materialPrice->getMaterial()->getId(),
            'thickness' => $materialPrice->getThickness(),
            'price' => $materialPrice->getPrice(),
        ];
    }
}
