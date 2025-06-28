<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

class ProductColorNotFoundException extends ProductException
{
    public static function forProduct(int $colorCode): self
    {
        return new self("Color '{$colorCode}' is not assigned to product");
    }
}
