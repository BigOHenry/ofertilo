<?php

declare(strict_types=1);

namespace App\Application\Material;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Exception\MaterialAlreadyExistsException;
use App\Domain\Material\Factory\MaterialFactory;
use App\Domain\Material\Repository\MaterialRepositoryInterface;
use App\Domain\Material\ValueObject\Type;
use App\Domain\Translation\Repository\TranslationLoaderInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class MaterialService
{
    public function __construct(
        private MaterialRepositoryInterface $materialRepository,
        private MaterialFactory $materialFactory,
        private TranslationLoaderInterface $translationLoader,
        private TranslatorInterface $translator,
    ) {
    }

    public function createEmpty(): Material
    {
        return $this->materialFactory->createEmpty();
    }

    public function create(Type $type, string $name): Material
    {
        if ($this->materialRepository->findByTypeAndName($type, $name)) {
            throw MaterialAlreadyExistsException::withTypeAndName($type, $name);
        }

        return $this->materialFactory->create($type, $name);
    }

    public function save(Material $material): void
    {
        $this->materialRepository->save($material);
    }

    public function delete(Material $material): void
    {
        $this->materialRepository->remove($material);
    }

    public function getPaginatedMaterials(Request $request): array
    {
        $page = max((int) $request->query->get('page', 1), 1);
        $size = min((int) $request->query->get('size', 10), 100);
        $offset = ($page - 1) * $size;

        $qb = $this->materialRepository->createQueryBuilder('m')
                                       ->setFirstResult($offset)
                                       ->setMaxResults($size)
        ;

        $sortData = $request->query->all('sort');
        $sortField = $sortData['field'] ?? null;
        $sortDir = $sortData['dir'] ?? 'asc';

        $allowedFields = ['name', 'type'];
        $allowedDirections = ['asc', 'desc'];

        if (\in_array($sortField, $allowedFields, true)
            && \in_array(mb_strtolower($sortDir), $allowedDirections, true)) {
            $qb->orderBy("m.$sortField", mb_strtoupper($sortDir));
        }

        $paginator = new Paginator($qb);
        $total = \count($paginator);

        $data = [];
        foreach ($paginator as $material) {
            $this->translationLoader->loadTranslations($material);
            $data[] = [
                'id' => $material->getId(),
                'name' => $material->getName(),
                'description' => $material->getDescription($request->getLocale()),
                'type' => $this->translator->trans(
                    'material.type.' . $material->getType()->value,
                    domain: 'enum'
                ),
            ];
        }

        return [
            'data' => $data,
            'last_page' => ceil($total / $size),
            'total' => $total,
        ];
    }

    public function addPriceToMaterial(Material $material, int $thickness, float $price): void
    {
        $material->addPrice($thickness, $price);
        $this->materialRepository->save($material);
    }

    public function updateMaterialPrice(MaterialPrice $materialPrice, int $thickness, float $price): void
    {
        $materialPrice->setThickness($thickness);
        $materialPrice->setPrice($price);
        $this->materialRepository->save($materialPrice->getMaterial());
    }

    public function removePriceFromMaterial(MaterialPrice $materialPrice): void
    {
        $material = $materialPrice->getMaterial();

        $material->removePrice($materialPrice);
        $this->materialRepository->save($material);
    }

    public function createEmptyPrice(Material $material): MaterialPrice
    {
        return MaterialPrice::createEmpty($material);
    }

    public function getMaterialPricesData(Material $material): array
    {
        $data = [];
        foreach ($material->getPrices() as $price) {
            $data[] = [
                'id' => $price->getId(),
                'thickness' => $price->getThickness(),
                'price' => $price->getPrice(),
                'formatted_price' => number_format($price->getPrice(), 2, ',', ' ') . ' Kč',
                'formatted_thickness' => $price->getThickness() . ' mm',
            ];
        }

        usort($data, static fn ($a, $b) => $a['thickness'] <=> $b['thickness']);

        return [
            'data' => $data,
            'material_id' => $material->getId(),
            'material_name' => $material->getName(),
            'total_prices' => \count($data),
        ];
    }
}
