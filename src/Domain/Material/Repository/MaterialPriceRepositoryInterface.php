<?php

declare(strict_types=1);

namespace App\Domain\Material\Repository;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use Doctrine\ORM\QueryBuilder;

interface MaterialPriceRepositoryInterface
{
    public function createQueryBuilder(string $alias): QueryBuilder;
    public function findByMaterialAndThickness(Material $material, int $thickness): ?MaterialPrice;
    public function findById(int $id): ?MaterialPrice;
    public function save(MaterialPrice $materialPrice): void;
    public function remove(MaterialPrice $materialPrice): void;
}
