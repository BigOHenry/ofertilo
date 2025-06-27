<?php

declare(strict_types=1);

namespace App\Domain\Material\Repository;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\ValueObject\Type;
use Doctrine\ORM\QueryBuilder;

interface MaterialRepositoryInterface
{
    public function createQueryBuilder(string $alias): QueryBuilder;

    public function findByTypeAndName(Type $type, string $name): ?Material;

    public function save(Material $material): void;

    public function remove(Material $material): void;
}
