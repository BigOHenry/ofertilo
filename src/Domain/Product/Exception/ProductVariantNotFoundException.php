<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

class ProductVariantNotFoundException extends ProductException
{
    public static function withId(string $id): self
    {
        return new self(\sprintf("ProductVariant '%s' not found", $id));
    }
}
