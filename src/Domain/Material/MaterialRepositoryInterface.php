<?php

declare(strict_types=1);

namespace App\Domain\Material;

interface MaterialRepositoryInterface
{
    public function findByName(string $name): ?Material;

    public function save(Material $material): void;

    public function delete(Material $material): void;
}
