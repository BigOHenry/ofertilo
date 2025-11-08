<?php

declare(strict_types=1);

namespace App\Domain\Wood\Exception;

class InvalidWoodException extends WoodException
{
    public static function emptyName(): self
    {
        return new self('Wood name cannot be empty');
    }

    public static function nameTooShort(int $minLength): self
    {
        return new self("Wood name must be at least {$minLength} characters");
    }

    public static function nameTooLong(int $maxLength): self
    {
        return new self("Wood name must be maximum {$maxLength} characters");
    }

    public static function nameInvalidCharacters(): self
    {
        return new self('Wood name contains invalid characters');
    }

    public static function latinNameTooLong(int $maxLength): self
    {
        return new self("Wood latin name must be maximum {$maxLength} characters");
    }

    public static function latinNameInvalidCharacters(): self
    {
        return new self('Wood latin name contains invalid characters');
    }

    public static function dryDensityTooLow(int $minValue): self
    {
        return new self("Wood dry density is lower than minimum allowed value {$minValue} kg/m³");
    }

    public static function dryDensityTooHigh(int $maxValue): self
    {
        return new self("Wood dry density exceeds maximum allowed value {$maxValue} kg/m³");
    }

    public static function hardnessTooLow(int $minValue): self
    {
        return new self("Wood hardness is lower than minimum allowed value {$minValue}");
    }

    public static function hardnessTooHigh(int $maxValue): self
    {
        return new self("Wood hardness exceeds maximum allowed value {$maxValue}");
    }

    public static function descriptionTooLong(int $maxLength): self
    {
        return new self("Wood description must be maximum {$maxLength} characters");
    }

    public static function placeOfOriginTooLong(int $maxLength): self
    {
        return new self("Wood place of origin must be maximum {$maxLength} characters");
    }

    public static function placeOfOriginInvalidCharacters(): self
    {
        return new self('Wood place of origin contains invalid characters');
    }
}
