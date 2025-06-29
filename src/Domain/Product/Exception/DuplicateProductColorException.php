<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

class DuplicateProductColorException extends ProductException
{
    public static function forProduct(int $colorCode): self
    {
        return new self("Color '{$colorCode}' is already assigned to product");
    }
}
