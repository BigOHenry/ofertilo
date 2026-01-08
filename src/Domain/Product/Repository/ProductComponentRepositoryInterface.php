<?php

declare(strict_types=1);

namespace App\Domain\Product\Repository;

use App\Domain\Product\Entity\ProductComponent;
use App\Domain\Product\Exception\ProductComponentNotFoundException;

interface ProductComponentRepositoryInterface
{
    public function findById(string $id): ?ProductComponent;

    /**
     * @throws ProductComponentNotFoundException
     */
    public function getById(string $id): ProductComponent;
}
