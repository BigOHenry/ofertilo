<?php

declare(strict_types=1);

namespace App\Application\Color;

use App\Application\Color\Command\CreateColorCommand;
use App\Application\Color\Command\EditColorCommand;
use App\Domain\Color\Entity\Color;
use App\Domain\Color\Exception\ColorAlreadyExistsException;
use App\Domain\Color\Exception\InvalidColorException;
use App\Domain\Color\Factory\ColorFactory;
use App\Domain\Color\Repository\ColorRepositoryInterface;
use App\Domain\Translation\Repository\TranslationLoaderInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ColorService
{
    public function __construct(
        private ColorRepositoryInterface $colorRepository,
        private ColorFactory $colorFactory,
        private TranslationLoaderInterface $translationLoader,
        private TranslatorInterface $translator,
    ) {
    }

    public function createFromCommand(CreateColorCommand $command): Color
    {
        $code = $command->getCode();

        if ($code === null) {
            throw InvalidColorException::emptyCode();
        }

        if ($this->colorRepository->findByCode($code)) {
            throw ColorAlreadyExistsException::withCode($code);
        }

        $color = Color::create($code);
        $color->setInStock($command->isInStock());
        $color->setEnabled($command->isEnabled());

        foreach ($command->getTranslations() as $translation) {
            $value = mb_trim($translation->getValue() ?? '');
            if (!empty($value)) {
                $color->setDescription($value, $translation->getLocale());
            } else {
                $color->setDescription(null, $translation->getLocale());
            }
        }

        $this->colorRepository->save($color);

        return $color;
    }

    public function updateFromCommand(Color $color, EditColorCommand $command): void
    {
        if (($color->getCode() !== $command->getCode()) && $this->colorRepository->findByCode($command->getCode())) {
            throw ColorAlreadyExistsException::withCode($command->getCode());
        }

        $color->setCode($command->getCode());
        $color->setInStock($command->isInStock());
        $color->setEnabled($command->isEnabled());

        foreach ($command->getTranslations() as $translation) {
            $value = mb_trim($translation->getValue() ?? '');
            if (!empty($value)) {
                $color->setDescription($value, $translation->getLocale());
            } else {
                $color->setDescription(null, $translation->getLocale());
            }
        }

        $this->colorRepository->save($color);
    }

    public function create(int $code): Color
    {
        if ($this->colorRepository->findByCode($code)) {
            throw ColorAlreadyExistsException::withCode($code);
        }

        return $this->colorFactory->create($code);
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
                'in_stock' => $this->translator->trans($color->isInStock() ? 'boolean.yes' : 'boolean.no', domain: 'messages'),
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
