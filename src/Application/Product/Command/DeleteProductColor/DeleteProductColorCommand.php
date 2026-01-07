<?php

declare(strict_types=1);

namespace App\Application\Product\Command\DeleteProductColor;

final readonly class DeleteProductColorCommand
{
    protected function __construct(
        public string $productId,
        public string $productColorId,
    ) {
    }

    public static function create(string $productId, string $productColorId): self
    {
        return new self($productId, $productColorId);
    }
}
