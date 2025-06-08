<?php

declare(strict_types=1);

namespace App\Domain\Material;

use Doctrine\ORM\QueryBuilder;

interface MaterialPriceRepositoryInterface
{
    public function createQueryBuilder(string $alias): QueryBuilder;

    public function save(MaterialPrice $price): void;

    public function remove(MaterialPrice $price): void;
}
