<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductVariantFormData;

use App\Domain\Product\Entity\ProductVariant;

final readonly class GetProductVariantFormDataQuery
{
    public function __construct(
        public string $productId,
        public string $productVariantId,
    ) {
    }

    public static function create(ProductVariant $productVariant): self
    {
        return new self($productVariant->getProduct()->getId(), $productVariant->getId());
    }
}
