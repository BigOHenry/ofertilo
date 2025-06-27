<?php

declare(strict_types=1);

namespace App\Domain\Color\Exception;

class InvalidColorException extends ColorException
{
    public static function emptyCode(): self
    {
        return new self('Color code cannot be empty');
    }

    public static function codeTooLow(int $minValue): self
    {
        return new self("Color code is lower than minimum allowed value {$minValue}");
    }

    public static function codeTooHigh(int $maxValue): self
    {
        return new self("Color code exceeds maximum allowed value {$maxValue}");
    }

    public static function descriptionTooLong(int $maxLength): self
    {
        return new self("Color description must be maximum {$maxLength} characters");
    }
}
