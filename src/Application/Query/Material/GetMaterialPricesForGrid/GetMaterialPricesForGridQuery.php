<?php

declare(strict_types=1);

namespace App\Application\Query\Material\GetMaterialPricesForGrid;

final readonly class GetMaterialPricesForGridQuery
{
    protected function __construct(private int $materialId)
    {
    }

    public static function create(int $materialId): self
    {
        return new self($materialId);
    }

    public function getMaterialId(): int
    {
        return $this->materialId;
    }
}
