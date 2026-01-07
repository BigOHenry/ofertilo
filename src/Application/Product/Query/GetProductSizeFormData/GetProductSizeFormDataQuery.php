<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductSizeFormData;

use App\Domain\Product\Entity\ProductSize;

final readonly class GetProductSizeFormDataQuery
{
    public function __construct(
        public string $productId,
        public string $productSizeId,
    ) {
    }

    public static function create(ProductSize $productSize): self
    {
        return new self($productSize->getProduct()->getId(), $productSize->getId());
    }
}
