<?php

declare(strict_types=1);

namespace App\Application\Query\Color\GetOutOfStockColorsGrid;

final readonly class GetOutOfStockColorsGridQuery
{
    protected function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }
}
