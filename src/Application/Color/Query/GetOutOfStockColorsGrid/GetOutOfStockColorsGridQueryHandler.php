<?php

declare(strict_types=1);

namespace App\Application\Color\Query\GetOutOfStockColorsGrid;

use App\Domain\Color\Entity\Color;
use App\Domain\Color\Repository\ColorRepositoryInterface;
use App\Infrastructure\Service\LocaleService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetOutOfStockColorsGridQueryHandler
{
    public function __construct(
        private ColorRepositoryInterface $colorRepository,
        private LocaleService $localeService,
    ) {
    }

    /**
     * @return array{data: array<array{id: string, code: int, description: string|null}>}
     */
    public function __invoke(GetOutOfStockColorsGridQuery $query): array
    {
        $colors = $this->colorRepository->findOutOfStock();
        $localeService = $this->localeService;
        $data = array_map(
            static function (Color $color) use ($localeService) {
                return [
                    'id' => $color->getId(),
                    'code' => $color->getCode(),
                    'description' => $color->getDescription($localeService->getCurrentLocale()),
                ];
            },
            $colors
        );

        return [
            'data' => $data,
        ];
    }
}
