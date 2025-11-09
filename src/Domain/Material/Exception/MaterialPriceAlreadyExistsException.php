<?php

declare(strict_types=1);

namespace App\Domain\Material\Exception;

class MaterialPriceAlreadyExistsException extends MaterialException
{
    public static function withThickness(int $thickness): self
    {
        return new self(\sprintf("Price for thickness %s mm already exists", $thickness));
    }
}
