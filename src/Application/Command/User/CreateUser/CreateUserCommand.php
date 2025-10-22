<?php

declare(strict_types=1);

namespace App\Application\Command\User\CreateUser;

use App\Domain\User\ValueObject\Role;

class CreateUserCommand
{
    /**
     * @param string|null $email
     * @param string|null $password
     * @param string|null $name
     * @param Role[]|string[] $roles
     * @param bool $forcePasswordChange
     * @param bool $two_fa_enabled
     */
    public function __construct(
        private readonly ?string $email,
        private ?string $password,
        private readonly ?string $name,
        private readonly array $roles,
        private readonly bool $forcePasswordChange = false,
        private readonly bool $two_fa_enabled = false
    ) {
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return Role[]|string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function isForcePasswordChange(): bool
    {
        return $this->forcePasswordChange;
    }

    public function isTwoFaEnabled(): bool
    {
        return $this->two_fa_enabled;
    }
}
