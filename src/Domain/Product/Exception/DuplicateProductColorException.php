<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

use App\Domain\Product\Entity\Product;

class DuplicateProductColorException extends ProductException
{
    public static function forProduct(Product $product, int $colorCode): self
    {
        return new self("Color '{$colorCode}' is already assigned to product {$product->getId()}");
    }
}
