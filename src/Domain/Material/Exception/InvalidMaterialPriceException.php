<?php

declare(strict_types=1);

namespace App\Domain\Material\Exception;

class InvalidMaterialPriceException extends MaterialException
{
    public static function priceTooLow(string $price, float $minPrice): self
    {
        return new self("Price {$price} is lower than minimum allowed price {$minPrice}");
    }

    public static function priceTooHigh(string $price, float $maxPrice): self
    {
        return new self("Price {$price} exceeds maximum allowed price {$maxPrice}");
    }

    public static function invalidPriceFormat(string $price): self
    {
        return new self("Invalid price format: '{$price}'");
    }

    public static function thicknessTooLow(float $thickness, float $minThickness): self
    {
        return new self("Price thickness {$thickness} is lower than minimum allowed thickness {$minThickness}");
    }

    public static function thicknessTooHigh(float $thickness, float $maxThickness): self
    {
        return new self("Price thickness {$thickness} exceeds maximum allowed thickness {$maxThickness}");
    }
}
