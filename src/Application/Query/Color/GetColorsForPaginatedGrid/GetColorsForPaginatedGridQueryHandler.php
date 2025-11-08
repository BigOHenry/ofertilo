<?php

declare(strict_types=1);

namespace App\Application\Query\Color\GetColorsForPaginatedGrid;

use App\Domain\Color\Entity\Color;
use App\Domain\Color\Repository\ColorRepositoryInterface;
use App\Infrastructure\Service\LocaleService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class GetColorsForPaginatedGridQueryHandler
{
    public function __construct(
        private ColorRepositoryInterface $colorRepository,
        private LocaleService $localeService,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array{data: list<array{id: int|null, code: int, description: string|null}>, total: int<0, max>}
     */
    public function __invoke(GetColorsForPaginatedGridQuery $query): array
    {
        $qb = $this->colorRepository->createQueryBuilder('c')
                                    ->setFirstResult($query->getOffset())
                                    ->setMaxResults($query->getSize())
        ;

        $sortField = $query->getSortField();
        $sortDir = $query->getSortDirection() ?? 'asc';

        $allowedFields = ['name', 'type'];
        $allowedDirections = ['asc', 'desc'];

        if (
            \in_array($sortField, $allowedFields, true)
            && \in_array(mb_strtolower($sortDir), $allowedDirections, true)
        ) {
            $qb->orderBy("m.$sortField", mb_strtoupper($sortDir));
        }

        $paginator = new Paginator($qb);
        $total = \count($paginator);

        $data = [];
        /** @var Color $color */
        foreach ($paginator as $color) {
            $data[] = [
                'id' => $color->getId(),
                'code' => $color->getCode(),
                'description' => $color->getDescription($this->localeService->getCurrentLocale()),
                'inStock' => $color->isInStock(),
                'enabled' => $this->translator->trans($color->isEnabled() ? 'boolean.yes' : 'boolean.no', domain: 'messages'),
            ];
        }

        usort($data, static fn ($a, $b) => $a['code'] <=> $b['code']);

        return [
            'data' => $data,
            'last_page' => ceil($total / $query->getSize()),
            'total' => $total,
        ];
    }
}
