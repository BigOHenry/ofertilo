<?php

declare(strict_types=1);

namespace App\Domain\Product\Repository;

use App\Domain\Product\Entity\ProductVariant;
use App\Domain\Product\Exception\ProductVariantNotFoundException;

interface ProductVariantRepositoryInterface
{
    public function findById(string $id): ?ProductVariant;

    /**
     * @throws ProductVariantNotFoundException
     */
    public function getById(string $id): ProductVariant;
}
