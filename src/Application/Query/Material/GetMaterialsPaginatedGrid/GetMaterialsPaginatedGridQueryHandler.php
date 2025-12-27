<?php

declare(strict_types=1);

namespace App\Application\Query\Material\GetMaterialsPaginatedGrid;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Repository\MaterialRepositoryInterface;
use App\Infrastructure\Service\LocaleService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class GetMaterialsPaginatedGridQueryHandler
{
    public function __construct(
        private MaterialRepositoryInterface $materialRepository,
        private LocaleService $localeService,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array{data: list<array{id: int|null, description: string|null, type: string, enabled: bool}>, last_page: int}
     */
    public function __invoke(GetMaterialsPaginatedGridQuery $query): array
    {
        $qb = $this->materialRepository->createQueryBuilder('m');

        $sortField = $query->getSortField();
        $sortDir = $query->getSortDirection() ?? 'asc';

        $allowedFields = ['wood'];
        $allowedDirections = ['asc', 'desc'];

        if (
            \in_array($sortField, $allowedFields, true)
            && \in_array(mb_strtolower($sortDir), $allowedDirections, true)
        ) {
            $qb->orderBy("m.$sortField", mb_strtoupper($sortDir));
        } else {
            $qb->orderBy('m.wood', 'ASC');
        }

        $qb->setFirstResult($query->getOffset())
           ->setMaxResults($query->getSize())
        ;

        $paginator = new Paginator($qb);
        $total = \count($paginator);

        $data = [];
        /** @var Material $material */
        foreach ($paginator as $material) {
            $data[] = [
                'id' => $material->getId(),
                'description' => $material->getWood()->getDescription($this->localeService->getCurrentLocale()),
                'type' => $this->translator->trans(
                    'material.type.' . $material->getType()->value,
                    domain: 'enum'
                ),
                'enabled' => $material->isEnabled(),
            ];
        }

        return [
            'data' => $data,
            'last_page' => (int) ceil($total / $query->getSize()),
        ];
    }
}
