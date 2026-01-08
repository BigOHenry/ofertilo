<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductVariantsGrid;

final readonly class GetProductVariantsGridQuery
{
    protected function __construct(private string $productId)
    {
    }

    public static function create(string $productId): self
    {
        return new self($productId);
    }

    public function getProductId(): string
    {
        return $this->productId;
    }
}
