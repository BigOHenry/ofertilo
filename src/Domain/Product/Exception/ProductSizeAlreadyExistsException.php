<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

use App\Domain\Shared\Exception\AlreadyExistsDomainException;

class ProductSizeAlreadyExistsException extends AlreadyExistsDomainException
{
    public static function withDimensions(int $length, int $width, ?int $thickness): self
    {
        if ($thickness !== null) {
            return new self(\sprintf("ProductSize '%s %s' is already assigned to product", $length, $width), 'height');
        }

        return new self(\sprintf("ProductSize '%s %s %s' is already assigned to product", $length, $width, $thickness), 'height');
    }
}
