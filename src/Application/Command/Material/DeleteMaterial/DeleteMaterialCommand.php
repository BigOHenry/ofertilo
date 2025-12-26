<?php

declare(strict_types=1);

namespace App\Application\Command\Material\DeleteMaterial;

final readonly class DeleteMaterialCommand
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
