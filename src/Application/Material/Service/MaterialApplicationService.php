<?php

declare(strict_types=1);

namespace App\Application\Material\Service;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Exception\MaterialNotFoundException;
use App\Domain\Material\Repository\MaterialRepositoryInterface;
use App\Domain\Material\ValueObject\MaterialType;
use App\Domain\Wood\Entity\Wood;

readonly class MaterialApplicationService
{
    public function __construct(
        private MaterialRepositoryInterface $materialRepository,
    ) {
    }

    public function findByWoodAndType(Wood $wood, MaterialType $type): ?Material
    {
        return $this->materialRepository->findByWoodAndType($wood, $type);
    }

    public function findById(int $id): ?Material
    {
        return $this->materialRepository->findById($id);
    }

    /**
     * @throws MaterialNotFoundException
     */
    public function getById(int $id): Material
    {
        return $this->materialRepository->getById($id);
    }

    public function save(Material $material): void
    {
        $this->materialRepository->save($material);
    }

    public function delete(Material $material): void
    {
        $this->materialRepository->remove($material);
    }
}
