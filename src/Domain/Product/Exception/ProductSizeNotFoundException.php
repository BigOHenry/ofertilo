<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

class ProductSizeNotFoundException extends ProductException
{
    public static function withId(string $id): self
    {
        return new self(\sprintf("ProductSize '%s' not found", $id));
    }
}
