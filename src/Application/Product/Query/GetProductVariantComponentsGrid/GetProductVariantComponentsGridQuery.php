<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductVariantComponentsGrid;

use App\Domain\Product\Entity\ProductVariant;

final readonly class GetProductVariantComponentsGridQuery
{
    protected function __construct(public string $productVariantId)
    {
    }

    public static function createFromProductVariant(ProductVariant $productVariant): self
    {
        return new self($productVariant->getId());
    }
}
