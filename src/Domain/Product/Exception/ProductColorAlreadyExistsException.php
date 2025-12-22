<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

use App\Domain\Shared\Exception\AlreadyExistsDomainException;

class ProductColorAlreadyExistsException extends AlreadyExistsDomainException
{
    public static function withCode(int $colorCode): self
    {
        return new self(\sprintf("Color '%s' is already assigned to product", $colorCode), 'color');
    }
}
