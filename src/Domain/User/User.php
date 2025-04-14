<?php
namespace App\Domain\User;

use App\Domain\Group\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
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
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private bool $needsPasswordChange = true;

    #[ORM\Column(type: "boolean")]
    private bool $forceEmailChange = true;

    #[ORM\ManyToMany(targetEntity: Group::class)]
    private ArrayCollection $groups;

    public function __construct(string $email)
    {
        $this->email = $email;
        $this->groups = new ArrayCollection();
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

    public function addGroup(Group $group): static
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getGroups(): ArrayCollection
    {
        return $this->groups;
    }

    public function setGroups(ArrayCollection $groups): void
    {
        $this->groups = $groups;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function isNeedsPasswordChange(): bool
    {
        return $this->needsPasswordChange;
    }

    public function setNeedsPasswordChange(bool $needsPasswordChange): void
    {
        $this->needsPasswordChange = $needsPasswordChange;
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

    public function removeGroup(Group $group): static
    {
        $this->groups->removeElement($group);
        return $this;
    }

    public function eraseCredentials(): void
    {
        // odstranění citlivých dat, pokud je potřeba
    }
}
