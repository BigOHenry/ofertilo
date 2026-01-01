<?php

declare(strict_types=1);

namespace App\Application\Material\Query\GetMaterialPricesGrid;

final readonly class GetMaterialPricesGridQuery
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
