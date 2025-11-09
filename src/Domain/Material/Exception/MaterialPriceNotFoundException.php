<?php

declare(strict_types=1);

namespace App\Domain\Material\Exception;

class MaterialPriceNotFoundException extends MaterialException
{
    public static function withThickness(int $thickness): self
    {
        return new self(\sprintf("Price for thickness %s mm not found", $thickness));
    }

    public static function withId(int $id): self
    {
        return new self(\sprintf("Price for id %s not found", $id));
    }
}
