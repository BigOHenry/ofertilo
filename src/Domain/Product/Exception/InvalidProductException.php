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

    public static function emptyType(): self
    {
        return new self('Product Type cannot be empty');
    }

    public static function emptyCountry(): self
    {
        return new self('Product Country cannot be empty');
    }
}
