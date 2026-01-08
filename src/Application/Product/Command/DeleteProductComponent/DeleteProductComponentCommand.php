<?php

declare(strict_types=1);

namespace App\Application\Product\Command\DeleteProductComponent;

final readonly class DeleteProductComponentCommand
{
    protected function __construct(
        public string $productComponentId,
    ) {
    }

    public static function create(string $productComponentId): self
    {
        return new self($productComponentId);
    }
}
