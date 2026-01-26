<?php

declare(strict_types=1);

namespace App\Application\Material\Command\DeleteMaterialPrice;

final readonly class DeleteMaterialPriceCommand
{
    private function __construct(
        public string $materialId,
        public string $priceId,
    ) {
    }

    public static function create(string $materialId, string $priceId): self
    {
        return new self($materialId, $priceId);
    }
}
