<?php

declare(strict_types=1);

namespace App\Domain\User;

use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'appuser')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 200)]
    private ?string $name = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private bool $forcePasswordChange = true;

    #[ORM\Column(type: 'boolean')]
    private bool $forceEmailChange = true;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $is_two_fa_enabled = false;

    #[ORM\Column(nullable: true)]
    private ?string $two_fa_secret = null;

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'jsonb')]
    private array $roles = [];

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        if (\in_array(Role::SUPER_ADMIN->value, $roles, true)) {
            $roles[] = Role::ADMIN->value;
            $roles[] = Role::WRITER->value;
            $roles[] = Role::READER->value;
        }

        if (\in_array(Role::ADMIN->value, $roles, true)) {
            $roles[] = Role::WRITER->value;
            $roles[] = Role::READER->value;
        }

        if (\in_array(Role::WRITER->value, $roles, true)) {
            $roles[] = Role::READER->value;
        }

        return array_unique(array_map(static fn ($r) => $r, $roles));
    }

    /**
     * @param string[]|Role[] $roles
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
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

    public function setForceEmailChange(bool $forceEmailChange): static
    {
        $this->forceEmailChange = $forceEmailChange;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // odstranění citlivých dat, pokud je potřeba
    }

    public function getTotpAuthenticationSecret(): ?string
    {
        return $this->two_fa_secret;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->email;
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->is_two_fa_enabled && $this->two_fa_secret !== null;
    }

    public function setTotpSecret(?string $secret): void
    {
        $this->two_fa_secret = $secret;
    }

    public function isTwoFactorEnabled(): bool
    {
        return $this->is_two_fa_enabled;
    }

    public function setTwoFactorEnabled(bool $enabled): void
    {
        $this->is_two_fa_enabled = $enabled;
    }

    public function getTotpAuthenticationConfiguration(): TotpConfigurationInterface|null
    {
        if (!$this->isTotpAuthenticationEnabled()) {
            return null;
        }

        return new TotpConfiguration($this->two_fa_secret, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }
}
