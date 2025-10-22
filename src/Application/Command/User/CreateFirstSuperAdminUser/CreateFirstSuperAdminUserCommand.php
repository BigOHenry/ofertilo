<?php

declare(strict_types=1);

namespace App\Application\Command\User\CreateFirstSuperAdminUser;

final readonly class CreateFirstSuperAdminUserCommand
{
    /**
     * @param string $email
     * @param string $password
     */
    public function __construct(
        private string $email,
        private string $password
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
