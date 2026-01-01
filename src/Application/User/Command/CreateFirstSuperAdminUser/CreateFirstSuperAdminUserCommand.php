<?php

declare(strict_types=1);

namespace App\Application\User\Command\CreateFirstSuperAdminUser;

final readonly class CreateFirstSuperAdminUserCommand
{
    public function __construct(
        private string $email,
        private string $password,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
