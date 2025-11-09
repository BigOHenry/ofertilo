<?php

declare(strict_types=1);

namespace App\Application\Command\Material\DeleteMaterialPrice;

final readonly class DeleteMaterialPriceCommand
{
    protected function __construct(
        private int $id,
    ) {
    }

    public static function create(int $id): self
    {
        return new self($id);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
