<?php

declare(strict_types=1);

namespace App\Application\Query\Material\GetMaterialPriceFormData;

use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Exception\MaterialPriceNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetMaterialPriceFormDataQueryHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    /**
     * @return array{id: int|null, wood: int|null, thickness: int, price: string}
     */
    public function __invoke(GetMaterialPriceFormDataQuery $query): array
    {
        $materialPrice = $this->materialApplicationService->findMaterialPriceById($query->getId());

        if ($materialPrice === null) {
            throw MaterialPriceNotFoundException::withId($query->getId());
        }

        return [
            'id' => $materialPrice->getId(),
            'wood' => $materialPrice->getMaterial()->getId(),
            'thickness' => $materialPrice->getThickness(),
            'price' => $materialPrice->getPrice(),
        ];
    }
}
