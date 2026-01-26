<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductColorsGrid;

final readonly class GetProductColorsGridQuery
{
    private function __construct(public string $productId)
    {
    }

    public static function create(string $productId): self
    {
        return new self($productId);
    }
}
