<?php

declare(strict_types=1);

namespace App\Domain\Product\Repository;

use App\Domain\Product\Entity\Product;
use Doctrine\ORM\QueryBuilder;

interface ProductRepositoryInterface
{
    public function createQueryBuilder(string $alias): QueryBuilder;

    public function findByName(string $name): ?Product;

    public function save(Product $product): void;

    public function remove(Product $product): void;
}
