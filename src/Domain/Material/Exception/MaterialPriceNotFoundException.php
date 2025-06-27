<?php

declare(strict_types=1);

namespace App\Domain\Material\Exception;

class MaterialPriceNotFoundException extends MaterialException
{
    public static function withThickness(int $thickness): self
    {
        return new self("Price for thickness {$thickness}mm not found");
    }
}
