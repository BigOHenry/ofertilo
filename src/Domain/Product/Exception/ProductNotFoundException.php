<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Shared\Country\Entity\Country;

class ProductNotFoundException extends ProductException
{
    public static function withTypeAndCountry(ProductType $type, Country $country): self
    {
        return new self(\sprintf("Product with type '%s' and country '%s' not found!", $type->value, $country->getAlpha3()));
    }

    public static function withId(string $id): self
    {
        return new self(\sprintf("Product with id '%s' not found!", $id));
    }
}
