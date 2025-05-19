<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class Country
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    private ?int $id = null;

    #[Column(type: 'text', length: 200, unique: true)]
    private ?string $name = null;

    #[Column(type: 'text', length: 2, unique: true)]
    private ?string $alpha2 = null;

    #[Column(type: 'text', length: 3, unique: true)]
    private ?string $alpha3 = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(?string $name): void {
        $this->name = $name;
    }

    public function getAlpha2(): ?string {
        return $this->alpha2;
    }

    public function setAlpha2(?string $alpha2): void {
        $this->alpha2 = $alpha2;
    }

    public function getAlpha3(): ?string {
        return $this->alpha3;
    }

    public function setAlpha3(?string $alpha3): void {
        $this->alpha3 = $alpha3;
    }
}
