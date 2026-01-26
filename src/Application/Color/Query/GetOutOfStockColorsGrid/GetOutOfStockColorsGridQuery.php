<?php

declare(strict_types=1);

namespace App\Application\Color\Query\GetOutOfStockColorsGrid;

final readonly class GetOutOfStockColorsGridQuery
{
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }
}
