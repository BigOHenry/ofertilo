<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Shared\Entity\Country;

class ProductAlreadyExistsException extends ProductException
{
    public static function withTypeAndCountry(ProductType $type, Country $country): self
    {
        return new self("Product with type '{$type->label()}' and country '{$country->getAlpha3()}' already exists");
    }
}
