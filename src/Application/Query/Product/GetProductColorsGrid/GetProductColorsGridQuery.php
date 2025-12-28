<?php

declare(strict_types=1);

namespace App\Application\Query\Product\GetProductColorsGrid;

final readonly class GetProductColorsGridQuery
{
    protected function __construct(private int $productId)
    {
    }

    public static function create(int $productId): self
    {
        return new self($productId);
    }

    public function getProductId(): int
    {
        return $this->productId;
    }
}
