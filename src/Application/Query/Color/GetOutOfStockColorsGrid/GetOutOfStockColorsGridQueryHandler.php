<?php

declare(strict_types=1);

namespace App\Application\Query\Color\GetOutOfStockColorsGrid;

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
     * @param GetOutOfStockColorsGridQuery $query
     * @return array{data: array{id: int, code: int, description: string, inStock: bool, enabled: bool}, last_page: int, total: int}
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

        usort($data, static fn ($a, $b) => $a['code'] <=> $b['code']);

        return [
            'data' => $data,
            'total' => \count($data),
        ];
    }
}
