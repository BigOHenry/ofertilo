<?php

declare(strict_types=1);

namespace App\Domain\Material;

interface MaterialPriceRepositoryInterface
{
    public function save(MaterialPrice $price): void;

    public function remove(MaterialPrice $price): void;
}
