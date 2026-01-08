<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductsPaginatedGrid;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Infrastructure\Service\LocaleService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class GetProductsPaginatedGridQueryHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private LocaleService $localeService,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array{data: list<array{id: string, name: string|null, country: string, type: string, enabled: bool}>, last_page: int}
     */
    public function __invoke(GetProductsPaginatedGridQuery $query): array
    {
        $qb = $this->productRepository->createQueryBuilder('w');

        $sortField = $query->getSortField();
        $sortDir = $query->getSortDirection() ?? 'asc';

        $allowedFields = ['id'];
        $allowedDirections = ['asc', 'desc'];

        if (
            \in_array($sortField, $allowedFields, true)
            && \in_array(mb_strtolower($sortDir), $allowedDirections, true)
        ) {
            $qb->orderBy("w.$sortField", mb_strtoupper($sortDir));
        } else {
            $qb->orderBy('w.id', 'ASC');
        }

        $qb->setFirstResult($query->getOffset())
           ->setMaxResults($query->getSize())
        ;

        $paginator = new Paginator($qb);
        $total = \count($paginator);

        $data = [];
        /** @var Product $product */
        foreach ($paginator as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName($this->localeService->getCurrentLocale()),
                'country' => $product->getCountry() ? '(' . $product->getCountry()->getAlpha2() . ') ' . $product->getCountry()->getName() : '',
                'type' => $this->translator->trans('product.type.' . $product->getType()->value, domain: 'enum'),
                'enabled' => $product->isEnabled(),
            ];
        }

        return [
            'data' => $data,
            'last_page' => (int) ceil($total / $query->getSize()),
        ];
    }
}
