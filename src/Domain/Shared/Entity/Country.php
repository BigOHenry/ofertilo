<?php

declare(strict_types=1);

namespace App\Domain\Shared\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'country')]
#[ORM\Index(name: 'idx_country_alpha2', columns: ['alpha2'])]
#[ORM\Index(name: 'idx_country_alpha3', columns: ['alpha3'])]
#[ORM\Index(name: 'idx_country_enabled', columns: ['enabled'])]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text', length: 200, unique: true)]
    private string $name;

    #[ORM\Column(type: 'text', length: 2, unique: true)]
    private string $alpha2;

    #[ORM\Column(type: 'text', length: 3, unique: true)]
    private string $alpha3;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $enabled;

    public function __construct(string $name, string $alpha2, string $alpha3, bool $enabled = true)
    {
        $this->name = $name;
        $this->alpha2 = mb_strtoupper($alpha2);
        $this->alpha3 = mb_strtoupper($alpha3);
        $this->enabled = $enabled;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAlpha2(): string
    {
        return $this->alpha2;
    }

    public function getAlpha3(): string
    {
        return $this->alpha3;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
