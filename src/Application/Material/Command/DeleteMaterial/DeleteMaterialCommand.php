<?php

declare(strict_types=1);

namespace App\Application\Material\Command\DeleteMaterial;

final readonly class DeleteMaterialCommand
{
    protected function __construct(
        private string $id,
    ) {
    }

    public static function create(string $id): self
    {
        return new self($id);
    }

    public function getId(): string
    {
        return $this->id;
    }
}
