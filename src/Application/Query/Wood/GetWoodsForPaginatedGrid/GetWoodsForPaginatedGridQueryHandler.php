<?php

declare(strict_types=1);

namespace App\Application\Query\Wood\GetWoodsForPaginatedGrid;

use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Repository\WoodRepositoryInterface;
use App\Infrastructure\Service\LocaleService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class GetWoodsForPaginatedGridQueryHandler
{
    public function __construct(
        private WoodRepositoryInterface $woodRepository,
        private LocaleService $localeService,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array{data: list<array{id: int|null, name: string, description: string|null, enabled: string}>, last_page: float, total: int<0, max>}
     */
    public function __invoke(GetWoodsForPaginatedGridQuery $query): array
    {
        $qb = $this->woodRepository->createQueryBuilder('w')
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
        /** @var Wood $wood */
        foreach ($paginator as $wood) {
            $data[] = [
                'id' => $wood->getId(),
                'name' => $wood->getName(),
                'description' => $wood->getDescription($this->localeService->getCurrentLocale()),
                'enabled' => $this->translator->trans($wood->isEnabled() ? 'boolean.yes' : 'boolean.no', domain: 'messages'),
            ];
        }

        usort($data, static fn ($a, $b) => $a['name'] <=> $b['name']);

        return [
            'data' => $data,
            'last_page' => ceil($total / $query->getSize()),
            'total' => $total,
        ];
    }
}
