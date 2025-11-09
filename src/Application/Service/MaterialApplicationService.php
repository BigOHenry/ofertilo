<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Repository\MaterialPriceRepositoryInterface;
use App\Domain\Material\Repository\MaterialRepositoryInterface;
use App\Domain\Material\ValueObject\Type;
use App\Domain\Wood\Entity\Wood;

final readonly class MaterialApplicationService
{
    public function __construct(
        private MaterialRepositoryInterface $materialRepository,
        private MaterialPriceRepositoryInterface $materialPriceRepository,
    ) {
    }

    public function findByWoodAndType(Wood $wood, Type $type): ?Material
    {
        return $this->materialRepository->findByWoodAndType($wood, $type);
    }

    public function findById(int $id): ?Material
    {
        return $this->materialRepository->findById($id);
    }

    public function findMaterialPriceByMaterialAndThickness(Material $material, int $thickness): ?MaterialPrice
    {
        return $this->materialPriceRepository->findByMaterialAndThickness($material, $thickness);
    }

    public function findMaterialPriceById(int $id): ?MaterialPrice
    {
        return $this->materialPriceRepository->findById($id);
    }

    public function save(Material $material): void
    {
        $this->materialRepository->save($material);
    }

    public function delete(Material $material): void
    {
        $this->materialRepository->remove($material);
    }

    public function saveMaterialPrice(MaterialPrice $materialPrice): void
    {
        $this->materialPriceRepository->save($materialPrice);
    }

    public function deleteMaterialPrice(MaterialPrice $materialPrice): void
    {
        $this->materialPriceRepository->remove($materialPrice);
    }

    public function removePriceFromMaterial(MaterialPrice $materialPrice): void
    {
        $material = $materialPrice->getMaterial();

        $material->removePrice($materialPrice);
        $this->materialRepository->save($material);
    }
}
