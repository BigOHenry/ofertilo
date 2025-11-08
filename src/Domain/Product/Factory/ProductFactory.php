<?php

declare(strict_types=1);

namespace App\Domain\Product\Factory;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;

readonly class ProductFactory
{
    public function __construct()
    {
    }

    public function create(Type $type, Country $country): Product
    {
        return Product::create($type, $country);
    }
}
