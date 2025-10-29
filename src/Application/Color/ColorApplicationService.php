<?php

declare(strict_types=1);

namespace App\Application\Color;

use App\Domain\Color\Entity\Color;
use App\Domain\Color\Repository\ColorRepositoryInterface;
use App\Domain\Translation\Repository\TranslationLoaderInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ColorApplicationService
{
    public function __construct(
        private ColorRepositoryInterface $colorRepository,
        private TranslationLoaderInterface $translationLoader,
        private TranslatorInterface $translator,
    ) {
    }

    public function findByCode(int $code): ?Color
    {
        return $this->colorRepository->findByCode($code);
    }

    public function findById(int $id): ?Color
    {
        return $this->colorRepository->findById($id);
    }

    public function save(Color $color): void
    {
        $this->colorRepository->save($color);
    }

    public function delete(Color $color): void
    {
        $this->colorRepository->remove($color);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPaginatedColors(Request $request): array
    {
        $page = max((int) $request->query->get('page', 1), 1);
        $size = min((int) $request->query->get('size', 10), 100);
        $offset = ($page - 1) * $size;

        $qb = $this->colorRepository->createQueryBuilder('m')
                                    ->setFirstResult($offset)
                                    ->setMaxResults($size)
        ;

        $sortData = $request->query->all('sort');
        $sortField = $sortData['field'] ?? null;
        $sortDir = $sortData['dir'] ?? 'asc';

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
        foreach ($paginator as $color) {
            $this->translationLoader->loadTranslations($color);
            $data[] = [
                'id' => $color->getId(),
                'code' => $color->getCode(),
                'description' => $color->getDescription($request->getLocale()),
                'inStock' => $this->translator->trans($color->isInStock() ? 'boolean.yes' : 'boolean.no', domain: 'messages'),
            ];
        }

        usort($data, static fn ($a, $b) => $a['code'] <=> $b['code']);

        return [
            'data' => $data,
            'last_page' => ceil($total / $size),
            'total' => $total,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getOutOfStockColors(Request $request): array
    {
        $colors = $this->colorRepository->findOutOfStock();
        $translationLoader = $this->translationLoader;

        $data = array_map(
            static function (Color $color) use ($request, $translationLoader) {
                $translationLoader->loadTranslations($color);

                return [
                    'id' => $color->getId(),
                    'code' => $color->getCode(),
                    'description' => $color->getDescription($request->getLocale()),
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
