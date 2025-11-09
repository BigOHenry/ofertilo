<?php

declare(strict_types=1);

namespace App\Domain\Product\Repository;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use Doctrine\ORM\QueryBuilder;

interface ProductRepositoryInterface
{
    public function createQueryBuilder(string $alias): QueryBuilder;

    public function findByTypeAndCountry(Type $type, Country $country): ?Product;
    public function findById(int $id): ?Product;

    public function save(Product $product): void;

    public function remove(Product $product): void;
}
