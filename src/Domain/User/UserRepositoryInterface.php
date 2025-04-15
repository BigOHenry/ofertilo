<?php

namespace App\Domain\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function save(User $user): void;
    public function hasSuperAdmin(): bool;
}