<?php

namespace App\Domain\Permission;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'permission')]
class Permission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $code;

    #[ORM\Column(length: 255)]
    private string $description;

    public function __construct(string $code, string $description)
    {
        $this->code = $code;
        $this->description = $description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}