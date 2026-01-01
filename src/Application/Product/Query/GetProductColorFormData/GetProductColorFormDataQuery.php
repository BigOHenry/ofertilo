<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductColorFormData;

use App\Application\Shared\Exception\DeveloperLogicException;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductColor;

final readonly class GetProductColorFormDataQuery
{
    public function __construct(
        public int $productId,
        public int $productColorId,
    ) {
    }

    public static function create(ProductColor $productColor): self
    {
        $productId = $productColor->getProduct()->getId();
        $productColorId = $productColor->getId();

        if ($productId === null) {
            throw DeveloperLogicException::becauseEntityIsNotPersisted(Product::class);
        }

        if ($productColorId === null) {
            throw DeveloperLogicException::becauseEntityIsNotPersisted(ProductColor::class);
        }

        return new self($productId, $productColorId);
    }
}
