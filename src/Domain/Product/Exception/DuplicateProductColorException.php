<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

class DuplicateProductColorException extends ProductException
{
    public static function withCode(int $colorCode): self
    {
        return new self(\sprintf("Color '%s' is already assigned to product", $colorCode));
    }
}
