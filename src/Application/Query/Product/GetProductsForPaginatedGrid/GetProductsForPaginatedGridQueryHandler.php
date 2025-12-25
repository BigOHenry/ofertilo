<?php

declare(strict_types=1);

namespace App\Application\Query\Product\GetProductsForPaginatedGrid;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Infrastructure\Service\LocaleService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class GetProductsForPaginatedGridQueryHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private LocaleService $localeService,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array{data: list<array{id: int|null, description: string|null, country: string, type: string, enabled: string}>,
     *      last_page: float, total: int<0, max>}
     */
    public function __invoke(GetProductsForPaginatedGridQuery $query): array
    {
        $qb = $this->productRepository->createQueryBuilder('w')
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
        /** @var Product $product */
        foreach ($paginator as $product) {
            $data[] = [
                'id' => $product->getId(),
                'description' => $product->getDescription($this->localeService->getCurrentLocale()),
                'country' => $product->getCountry() ? '(' . $product->getCountry()->getAlpha2() . ') ' . $product->getCountry()->getName() : '',
                'type' => $this->translator->trans('product.type.' . $product->getType()->value, domain: 'enum'),
                'enabled' => $this->translator->trans($product->isEnabled() ? 'boolean.yes' : 'boolean.no', domain: 'messages'),
            ];
        }

        return [
            'data' => $data,
            'last_page' => ceil($total / $query->getSize()),
            'total' => $total,
        ];
    }
}
