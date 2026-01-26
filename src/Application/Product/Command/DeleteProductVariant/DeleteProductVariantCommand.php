<?php

declare(strict_types=1);

namespace App\Application\Product\Command\DeleteProductVariant;

final readonly class DeleteProductVariantCommand
{
    private function __construct(
        public string $productId,
        public string $productVariantId,
    ) {
    }

    public static function create(string $productId, string $productVariantId): self
    {
        return new self($productId, $productVariantId);
    }
}
