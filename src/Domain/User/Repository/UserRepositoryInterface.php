<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserNotFoundException;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    /**
     * @throws UserNotFoundException
     */
    public function getById(string $id): User;

    /**
     * @throws UserNotFoundException
     */
    public function getByEmail(string $email): User;

    public function hasSuperAdmin(): bool;

    public function remove(User $user): void;
}
