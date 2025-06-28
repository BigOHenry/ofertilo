<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

use App\Domain\Product\Entity\Product;

class ProductColorNotFoundException extends ProductException
{
    public static function forProduct(Product $product, int $colorCode): self
    {
        return new self("Color '{$colorCode}' is not assigned to product {$product->getId()}");
    }
}
