<?php

declare(strict_types=1);

namespace App\Application\Wood\Query\GetWoodsPaginatedGrid;

use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Repository\WoodRepositoryInterface;
use App\Infrastructure\Service\LocaleService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetWoodsPaginatedGridQueryHandler
{
    public function __construct(
        private WoodRepositoryInterface $woodRepository,
        private LocaleService $localeService,
    ) {
    }

    /**
     * @return array{data: list<array{id: string, name: string, description: string|null, enabled: bool}>, last_page: float}
     */
    public function __invoke(GetWoodsPaginatedGridQuery $query): array
    {
        $qb = $this->woodRepository->createQueryBuilder('w');

        $sortField = $query->getSortField();
        $sortDir = $query->getSortDirection() ?? 'asc';

        $allowedFields = ['name'];
        $allowedDirections = ['asc', 'desc'];

        if (
            \in_array($sortField, $allowedFields, true)
            && \in_array(mb_strtolower($sortDir), $allowedDirections, true)
        ) {
            $qb->orderBy("w.$sortField", mb_strtoupper($sortDir));
        } else {
            $qb->orderBy('w.name', 'ASC');
        }

        $qb->setFirstResult($query->getOffset())
           ->setMaxResults($query->getSize())
        ;

        $paginator = new Paginator($qb);
        $total = \count($paginator);

        $data = [];
        /** @var Wood $wood */
        foreach ($paginator as $wood) {
            $data[] = [
                'id' => $wood->getId(),
                'name' => $wood->getName(),
                'description' => $wood->getDescription($this->localeService->getCurrentLocale()),
                'enabled' => $wood->isEnabled(),
            ];
        }

        return [
            'data' => $data,
            'last_page' => (int) ceil($total / $query->getSize()),
        ];
    }
}
