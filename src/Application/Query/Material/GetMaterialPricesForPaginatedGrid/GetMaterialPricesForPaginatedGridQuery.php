<?php

declare(strict_types=1);

namespace App\Application\Query\Material\GetMaterialPricesForPaginatedGrid;

use App\Domain\Material\Entity\Material;

final readonly class GetMaterialPricesForPaginatedGridQuery
{
    protected function __construct(private Material $material)
    {
    }

    public static function create(Material $material): self
    {
        return new self($material);
    }

    public function getMaterial(): Material
    {
        return $this->material;
    }
}
