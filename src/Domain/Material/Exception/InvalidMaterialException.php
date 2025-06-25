<?php

declare(strict_types=1);

namespace App\Domain\Material\Exception;

class InvalidMaterialException extends MaterialException
{
    public static function emptyName(): self
    {
        return new self('Material name cannot be empty');
    }

    public static function nameTooShort(int $minLength): self
    {
        return new self("Material name must be at least {$minLength} characters");
    }

    public static function nameTooLong(int $maxLength): self
    {
        return new self("Material name must be maximum {$maxLength} characters");
    }

    public static function nameInvalidCharacters(): self
    {
        return new self('Material name contains invalid characters');
    }

    public static function latinNameTooLong(int $maxLength): self
    {
        return new self("Material latin name must be maximum {$maxLength} characters");
    }

    public static function latinNameInvalidCharacters(): self
    {
        return new self('Material latin name contains invalid characters');
    }

    public static function dryDensityTooLow(int $minValue): self
    {
        return new self("Material dry density is lower than minimum allowed value {$minValue}kg/m³");
    }

    public static function dryDensityTooHigh(int $maxValue): self
    {
        return new self("Material dry density exceeds maximum allowed value {$maxValue}kg/m³");
    }

    public static function hardnessTooLow(int $minValue): self
    {
        return new self("Material hardness is lower than minimum allowed value {$minValue}kg/m³");
    }

    public static function hardnessTooHigh(int $maxValue): self
    {
        return new self("Material hardness exceeds maximum allowed value {$maxValue}kg/m³");
    }

    public static function descriptionTooLong(int $maxLength): self
    {
        return new self("Material description must be maximum {$maxLength} characters");
    }

    public static function placeOfOriginTooLong(int $maxLength): self
    {
        return new self("Material place of origin must be maximum {$maxLength} characters");
    }

    public static function placeOfOriginInvalidCharacters(): self
    {
        return new self('Material place of origin contains invalid characters');
    }
}