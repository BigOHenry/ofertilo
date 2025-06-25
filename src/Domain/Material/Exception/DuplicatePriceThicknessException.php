<?php

namespace App\Domain\Material\Exception;

class DuplicatePriceThicknessException extends MaterialException
{
    public static function forThickness(int $thickness): self
    {
        return new self("Price for thickness {$thickness}mm already exists");
    }
}