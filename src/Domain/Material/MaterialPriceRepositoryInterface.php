<?php

namespace App\Domain\Material;

interface MaterialPriceRepositoryInterface
{
    public function save(MaterialPrice $price): void;
    public function remove(MaterialPrice $price): void;
}
