<?php

declare(strict_types=1);

namespace App\Application\Product\Command\DeleteProductSize;

final readonly class DeleteProductSizeCommand
{
    protected function __construct(
        public string $productId,
        public string $productSizeId,
    ) {
    }

    public static function create(string $productId, string $productSizeId): self
    {
        return new self($productId, $productSizeId);
    }
}
