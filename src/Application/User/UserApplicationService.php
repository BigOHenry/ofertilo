<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Role;

readonly class UserApplicationService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function isSystemInstalled(): bool
    {
        return $this->userRepository->hasSuperAdmin();
    }

    public function save(User $user): void
    {
        $this->userRepository->save($user);
    }

    public function delete(User $user): void
    {
        $this->userRepository->remove($user);
    }

    public function findByEmail(string $email): ?User
    {
       return $this->userRepository->findByEmail($email);
    }

    /**
     * @param string[]|Role[] $roles
     */
    public function isSuperAdmin(array $roles): bool
    {
        return \in_array(Role::SUPER_ADMIN, $roles, true)
            || \in_array(Role::SUPER_ADMIN->value, $roles, true);
    }
}
