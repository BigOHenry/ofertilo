<?php

declare(strict_types=1);

namespace App\Application\Material\Query\GetMaterialPricesGrid;

final readonly class GetMaterialPricesGridQuery
{
    protected function __construct(public string $materialId)
    {
    }

    public static function create(string $materialId): self
    {
        return new self($materialId);
    }
}
