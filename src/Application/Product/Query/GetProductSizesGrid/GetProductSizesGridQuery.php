<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductSizesGrid;

final readonly class GetProductSizesGridQuery
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
