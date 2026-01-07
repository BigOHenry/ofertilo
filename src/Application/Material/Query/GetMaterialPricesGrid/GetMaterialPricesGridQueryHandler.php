<?php

declare(strict_types=1);

namespace App\Application\Material\Query\GetMaterialPricesGrid;

use App\Application\Material\Service\MaterialApplicationService;
use App\Domain\Material\Exception\MaterialNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetMaterialPricesGridQueryHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    /**
     * @return list<array{id: string, thickness: int, price: string, formatted_price: non-falsy-string, formatted_thickness: non-falsy-string}>
     */
    public function __invoke(GetMaterialPricesGridQuery $query): array
    {
        $material = $this->materialApplicationService->findById($query->materialId);

        if ($material === null) {
            throw MaterialNotFoundException::withId($query->materialId);
        }

        $data = [];
        foreach ($material->getPrices() as $price) {
            $data[] = [
                'id' => $price->getId(),
                'thickness' => $price->getThickness(),
                'price' => $price->getPrice(),
                'formatted_price' => $price->getPrice() . ' Kč',
                'formatted_thickness' => $price->getThickness() . ' mm',
            ];
        }

        usort($data, static fn ($a, $b) => $a['thickness'] <=> $b['thickness']);

        return $data;
    }
}
