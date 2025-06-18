<?php

declare(strict_types=1);

namespace App\Domain\Product\Repository;

use App\Domain\Product\Entity\ProductColor;
use Doctrine\ORM\QueryBuilder;

interface ProductColorRepositoryInterface
{
    public function createQueryBuilder(string $alias): QueryBuilder;

    public function save(ProductColor $productColor): void;

    public function remove(ProductColor $productColor): void;
}
