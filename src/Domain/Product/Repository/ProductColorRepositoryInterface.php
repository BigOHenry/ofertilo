<?php

declare(strict_types=1);

namespace App\Domain\Product\Repository;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductColor;
use Doctrine\ORM\QueryBuilder;

interface ProductColorRepositoryInterface
{
    public function createQueryBuilder(string $alias): QueryBuilder;

    public function findByProductAndColor(Product $product, Color $color): ?ProductColor;
    public function findById(int $id): ?ProductColor;

    public function save(ProductColor $productColor): void;

    public function remove(ProductColor $productColor): void;
}
