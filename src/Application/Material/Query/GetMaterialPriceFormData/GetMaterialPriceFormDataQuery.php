<?php

declare(strict_types=1);

namespace App\Application\Material\Query\GetMaterialPriceFormData;

use App\Domain\Material\Entity\MaterialPrice;

readonly class GetMaterialPriceFormDataQuery
{
    protected function __construct(
        public string $materialId,
        public string $priceId,
    ) {
    }

    public static function create(MaterialPrice $materialPrice): self
    {
        return new self($materialPrice->getMaterial()->getId(), $materialPrice->getId());
    }
}
