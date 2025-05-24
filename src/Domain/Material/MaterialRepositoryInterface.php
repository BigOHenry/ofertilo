<?php

declare(strict_types=1);

namespace App\Domain\Material;

use Doctrine\ORM\QueryBuilder;

interface MaterialRepositoryInterface
{
    public function createQueryBuilder(string $alias): QueryBuilder;

    public function findByName(string $name): ?Material;

    public function save(Material $material): void;

    public function remove(Material $material): void;
}
