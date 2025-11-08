<?php

declare(strict_types=1);

namespace App\Domain\Material\Repository;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\ValueObject\Type;
use App\Domain\Wood\Entity\Wood;
use Doctrine\ORM\QueryBuilder;

interface MaterialRepositoryInterface
{
    public function createQueryBuilder(string $alias): QueryBuilder;

    public function findByWoodAndType(Wood $wood, Type $type): ?Material;
    public function findById(int $id): ?Material;

    public function save(Material $material): void;

    public function remove(Material $material): void;
}
