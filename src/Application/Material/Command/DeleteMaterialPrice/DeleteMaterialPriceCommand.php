<?php

declare(strict_types=1);

namespace App\Application\Material\Command\DeleteMaterialPrice;

final readonly class DeleteMaterialPriceCommand
{
    protected function __construct(
        private int $materialId,
        private int $priceId,
    ) {
    }

    public static function create(int $materialId, int $priceId): self
    {
        return new self($materialId, $priceId);
    }

    public function getMaterialId(): int
    {
        return $this->materialId;
    }

    public function getPriceId(): int
    {
        return $this->priceId;
    }
}
