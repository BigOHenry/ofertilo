<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Domain\User\Exception\InvalidUserException;
use App\Domain\User\ValueObject\Role;
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
    private ?int $id;

    #[ORM\Column(name: 'email', type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 200, nullable: false)]
    private string $password;

    #[ORM\Column(type: 'string', length: 200, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $forcePasswordChange = false;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $two_fa_enabled = false;

    #[ORM\Column(nullable: true)]
    private ?string $two_fa_secret = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $active = true;

    /**
     * @var string[]|Role[]
     */
    #[ORM\Column(type: 'jsonb')]
    private array $roles = [];

    public static function create(string $email, string $password, string $name, Role $role = Role::READER): self
    {
        $user = new self();
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setName($name);
        $user->setRoles([$role]);

        return $user;
    }
//
//    /**
//     * @param string[]|Role[] $roles
//     */
//    public static function createFromDatabase(
//        int $id,
//        string $email,
//        string $password,
//        string $name,
//        bool $forcePasswordChange = false,
//        bool $forceEmailChange = false,
//        bool $is_two_fa_enabled = false,
//        ?string $two_fa_secret = null,
//        array $roles = [],
//    ): self {
//        $user = new self($id);
//        $user->setEmail($email);
//        $user->password = $password;
//        $user->name = $name;
//        $user->forcePasswordChange = $forcePasswordChange;
//        $user->forceEmailChange = $forceEmailChange;
//        $user->two_fa_enabled = $is_two_fa_enabled;
//        $user->two_fa_secret = $two_fa_secret;
//        $user->roles = $roles;
//
//        return $user;
//    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->validateEmail($email);
        $this->email = mb_strtolower(mb_trim($email));
    }

    public function getUserIdentifier(): string
    {
        \assert($this->email !== '', 'Email should never be empty');

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

        return array_unique(array_map(
            static fn ($r) => $r instanceof Role ? $r->value : (string) $r,
            $roles
        ));
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

    public function setName(string $name): void
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
        return $this->two_fa_enabled && $this->two_fa_secret !== null;
    }

    public function setTotpSecret(?string $secret): void
    {
        $this->two_fa_secret = $secret;
    }

    public function isTwoFactorEnabled(): bool
    {
        return $this->two_fa_enabled;
    }

    public function setTwoFactorEnabled(bool $enabled): void
    {
        $this->two_fa_enabled = $enabled;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        if (!$this->isTotpAuthenticationEnabled()) {
            return null;
        }

        if ($this->two_fa_secret === null) {
            throw new \InvalidArgumentException('two_fa_secret cannot be empty');
        }

        return new TotpConfiguration($this->two_fa_secret, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }

    protected function setId(?int $id): void
    {
        $this->id = $id;
    }

    private function validateEmail(string $email): void
    {
        $trimmed = mb_trim($email);

        if ($trimmed === '') {
            throw InvalidUserException::emptyEmail();
        }

        if (!filter_var($trimmed, \FILTER_VALIDATE_EMAIL)) {
            throw InvalidUserException::invalidEmailFormat($trimmed);
        }
    }
}
