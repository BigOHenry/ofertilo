<?php

declare(strict_types=1);

namespace App\Application\Material\Command;

use App\Domain\Material\Entity\Material;
use Symfony\Component\Validator\Constraints as Assert;

class CreateMaterialPriceCommand
{
    private Material $material;

    #[Assert\NotNull(message: 'not_null')]
    #[Assert\Range(
        notInRangeMessage: 'range',
        min: 1,
        max: 100
    )]
    private ?int $thickness = null;

    #[Assert\NotNull(message: 'not_null')]
    #[Assert\Range(
        notInRangeMessage: 'range',
        min: 1.00,
        max: 999999.99
    )]
    private ?string $price = null;

    public function __construct(Material $material)
    {
        $this->material = $material;
    }

    public function getMaterial(): Material
    {
        return $this->material;
    }

    public function getThickness(): ?int
    {
        return $this->thickness;
    }

    public function setThickness(?int $thickness): void
    {
        $this->thickness = $thickness;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): void
    {
        $this->price = $price;
    }
}
