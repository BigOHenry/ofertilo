<?php

declare(strict_types=1);

namespace App\Application\Product\Command\DeleteProductColor;

final readonly class DeleteProductColorCommand
{
    protected function __construct(
        public int $productId,
        public int $productColorId,
    ) {
    }

    public static function create(int $productId, int $productColorId): self
    {
        return new self($productId, $productColorId);
    }
}
