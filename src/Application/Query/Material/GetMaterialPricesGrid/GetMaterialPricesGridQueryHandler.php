<?php

declare(strict_types=1);

namespace App\Application\Query\Material\GetMaterialPricesGrid;

use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Exception\MaterialNotFoundException;
use App\Infrastructure\Service\LocaleService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetMaterialPricesGridQueryHandler
{
    public function __construct(
        private LocaleService $localeService,
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    /**
     * @return array{data: list<array{id: int|null, thickness: int, price: string, formatted_price: non-falsy-string,
     *      formatted_thickness: non-falsy-string}>, material_id: int|null, material_name: string, material_description: string,
     *      total_prices: int<0, max>}
     */
    public function __invoke(GetMaterialPricesGridQuery $query): array
    {
        $material = $this->materialApplicationService->findById($query->getMaterialId());

        if ($material === null) {
            throw MaterialNotFoundException::withId($query->getMaterialId());
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

        return [
            'data' => $data,
            'material_id' => $material->getId(),
            'material_name' => $material->getName(),
            'material_description' => $material->getDescription($this->localeService->getCurrentLocale()),
            'total_prices' => \count($data),
        ];
    }
}
