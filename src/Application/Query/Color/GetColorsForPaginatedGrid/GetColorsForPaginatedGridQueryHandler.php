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
     * @return array{data: list<array{id: int|null, code: int, description: string|null}>}
     */
    public function __invoke(GetColorsForPaginatedGridQuery $query): array
    {
        $qb = $this->colorRepository->createQueryBuilder('c');

        $sortField = $query->getSortField();
        $sortDir = $query->getSortDirection() ?? 'asc';

        $allowedFields = ['code', 'inStock'];
        $allowedDirections = ['asc', 'desc'];

        if (
            \in_array($sortField, $allowedFields, true)
            && \in_array(mb_strtolower($sortDir), $allowedDirections, true)
        ) {
            $qb->orderBy("c.$sortField", mb_strtoupper($sortDir));
        } else {
            $qb->orderBy('c.code', 'ASC');
        }

        $qb->setFirstResult($query->getOffset())
           ->setMaxResults($query->getSize())
        ;

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

        return [
            'data' => $data,
            'last_page' => (int) ceil($total / $query->getSize()),
        ];
    }
}
