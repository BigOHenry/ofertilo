<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

class InvalidProductColorException extends ProductException
{
    public static function descriptionTooLong(int $maxLength): self
    {
        return new self("ProductColor description must be maximum {$maxLength} characters");
    }
}
