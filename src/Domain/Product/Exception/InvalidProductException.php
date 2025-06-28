<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

class InvalidProductException extends ProductException
{
    public static function emptyColorDescription(): self
    {
        return new self('Color description cannot be empty');
    }

    public static function colorDescriptionTooLong(int $maxLength): self
    {
        return new self("Color description cannot exceed {$maxLength} characters");
    }
}
