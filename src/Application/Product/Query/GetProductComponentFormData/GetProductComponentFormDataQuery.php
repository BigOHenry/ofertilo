<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductComponentFormData;

use App\Domain\Product\Entity\ProductComponent;

final readonly class GetProductComponentFormDataQuery
{
    public function __construct(
        public string $productComponentId,
    ) {
    }

    public static function create(ProductComponent $productComponent): self
    {
        return new self($productComponent->getId());
    }
}
