<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

class ProductColorNotFoundException extends ProductException
{
    public static function withProduct(int $colorCode): self
    {
        return new self(\sprintf("Color '%s' is not assigned to product", $colorCode));
    }

    public static function withId(int $id): self
    {
        return new self(\sprintf("ProductColor '%s' not found", $id));
    }
}
