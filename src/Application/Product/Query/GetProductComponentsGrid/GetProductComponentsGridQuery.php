<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductComponentsGrid;

use App\Domain\Product\Entity\ProductVariant;

final readonly class GetProductComponentsGridQuery
{
    private function __construct(public string $productVariantId)
    {
    }

    public static function createFromProductVariant(ProductVariant $productVariant): self
    {
        return new self($productVariant->getId());
    }
}
