<?php

declare(strict_types=1);

namespace App\Application\User\Command;

use App\Domain\User\ValueObject\Role;

class CreateUserCommand
{
    private ?string $email = null;
    private ?string $password = null;
    private ?string $name = null;

    /**
     * @var string[]|Role[]
     */
    private array $roles = [];
    private bool $forcePasswordChange = false;
    private bool $forceEmailChange = false;
    private bool $two_fa_enabled = false;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
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

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Role[]|string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string[]|Role[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function isForcePasswordChange(): bool
    {
        return $this->forcePasswordChange;
    }

    public function setForcePasswordChange(bool $forcePasswordChange): void
    {
        $this->forcePasswordChange = $forcePasswordChange;
    }

    public function isForceEmailChange(): bool
    {
        return $this->forceEmailChange;
    }

    public function setForceEmailChange(bool $forceEmailChange): void
    {
        $this->forceEmailChange = $forceEmailChange;
    }

    public function isTwoFaEnabled(): bool
    {
        return $this->two_fa_enabled;
    }

    public function setTwoFaEnabled(bool $two_fa_enabled): void
    {
        $this->two_fa_enabled = $two_fa_enabled;
    }
}
