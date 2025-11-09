<?php

declare(strict_types=1);

namespace App\Application\Query\Material\GetMaterialPricesForPaginatedGrid;

use App\Infrastructure\Service\LocaleService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetMaterialPricesForPaginatedGridQueryHandler
{
    public function __construct(
        private LocaleService $localeService,
    ) {
    }

    /**
     * @return array{data: list<array{id: int|null, thickness: int, price: string, formatted_price: non-falsy-string,
     *      formatted_thickness: non-falsy-string}>, material_id: int|null, material_name: string, material_description: string,
     *      total_prices: int<0, max>}
     */
    public function __invoke(GetMaterialPricesForPaginatedGridQuery $query): array
    {
        $material = $query->getMaterial();
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
