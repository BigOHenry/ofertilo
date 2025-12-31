<?php

declare(strict_types=1);

namespace App\Application\Query\Material\GetMaterialPriceFormData;

use App\Application\Exception\DeveloperLogicException;
use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;

readonly class GetMaterialPriceFormDataQuery
{
    protected function __construct(
        public int $materialId,
        public int $priceId,
    ) {
    }

    public static function create(MaterialPrice $materialPrice): self
    {
        $materialId = $materialPrice->getMaterial()->getId();
        $materialPriceId = $materialPrice->getId();

        if ($materialId === null) {
            throw DeveloperLogicException::becauseEntityIsNotPersisted(Material::class);
        }

        if ($materialPriceId === null) {
            throw DeveloperLogicException::becauseEntityIsNotPersisted(MaterialPrice::class);
        }

        return new self($materialId, $materialPriceId);
    }
}
