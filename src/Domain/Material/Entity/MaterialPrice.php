<?php

declare(strict_types=1);

namespace App\Domain\Material\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'material_price')]
#[ORM\UniqueConstraint(
    name: 'unique_material_thickness',
    columns: ['material_id', 'thickness']
)]
#[UniqueEntity(
    fields: ['material_id', 'thickness'],
    message: 'unique',
)]
class MaterialPrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $thickness = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: false)]
    private ?float $price = null;

    #[ORM\ManyToOne(targetEntity: Material::class, inversedBy: 'prices')]
    #[ORM\JoinColumn(nullable: false)]
    private Material $material;

    public function __construct(Material $material)
    {
        $this->material = $material;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getThickness(): ?int
    {
        return $this->thickness;
    }

    public function setThickness(int $thickness): void
    {
        $this->thickness = $thickness;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getMaterial(): Material
    {
        return $this->material;
    }

    public function setMaterial(Material $material): void
    {
        $this->material = $material;
    }
}
