<?php

namespace App\Domain\Material;

use App\Domain\User\User;

interface MaterialRepositoryInterface
{
    public function findByName(string $name): ?Material;
    public function save(Material $material): void;
    public function delete(Material $material): void;
}