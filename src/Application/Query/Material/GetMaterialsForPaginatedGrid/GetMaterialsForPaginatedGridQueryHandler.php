<?php

declare(strict_types=1);

namespace App\Application\Query\Material\GetMaterialsForPaginatedGrid;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Repository\MaterialRepositoryInterface;
use App\Infrastructure\Service\LocaleService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class GetMaterialsForPaginatedGridQueryHandler
{
    public function __construct(
        private MaterialRepositoryInterface $materialRepository,
        private LocaleService $localeService,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @param GetMaterialsForPaginatedGridQuery $query
     * @return array{data: array{id: int, name: string, description: string, type: string, enabled: bool}, last_page: int, total: int}
     */
    public function __invoke(GetMaterialsForPaginatedGridQuery $query): array
    {
        $qb = $this->materialRepository->createQueryBuilder('c')
                                       ->setFirstResult($query->getOffset())
                                       ->setMaxResults($query->getSize());

        $sortField = $query->getSortField();
        $sortDir = $query->getSortDirection();

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

        dump($this->localeService->getCurrentLocale());

        $data = [];
        /** @var Material $material */
        foreach ($paginator as $material) {
            dump($material->getWood());
            dump($material->getWood()->getDescription());
            dump($material->getWood()->getDescription($this->localeService->getCurrentLocale()));
            $data[] = [
                'id' => $material->getId(),
                'name' => $material->getWood()->getName() . '_' . $material->getType()->value,
                'description' => $material->getWood()->getDescription($this->localeService->getCurrentLocale()),
                'type' => $this->translator->trans(
                    'material.type.' . $material->getType()->value,
                    domain: 'enum'
                ),
                'enabled' => $this->translator->trans($material->isEnabled() ? 'boolean.yes' : 'boolean.no', domain: 'messages'),
            ];
        }

        usort($data, static fn ($a, $b) => $a['description'] <=> $b['description']);

        return [
            'data' => $data,
            'last_page' => ceil($total / $query->getSize()),
            'total' => $total,
        ];
    }
}
