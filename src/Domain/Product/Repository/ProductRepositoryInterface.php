<?php

declare(strict_types=1);

namespace App\Domain\Product\Repository;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Exception\ProductNotFoundException;
use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Shared\Country\Entity\Country;
use Doctrine\ORM\QueryBuilder;

interface ProductRepositoryInterface
{
    public function createQueryBuilder(string $alias): QueryBuilder;

    public function findByTypeAndCountry(ProductType $type, Country $country): ?Product;

    public function findById(string $id): ?Product;

    /**
     * @throws ProductNotFoundException
     */
    public function getById(string $id): Product;

    public function save(Product $product): void;

    public function remove(Product $product): void;
}
