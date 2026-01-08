<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductColorFormData;

use App\Domain\Product\Entity\ProductColor;

final readonly class GetProductColorFormDataQuery
{
    public function __construct(
        public string $productId,
        public string $productColorId,
    ) {
    }

    public static function create(ProductColor $productColor): self
    {
        return new self($productColor->getProduct()->getId(), $productColor->getId());
    }
}
