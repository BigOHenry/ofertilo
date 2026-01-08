<?php

declare(strict_types=1);

namespace App\Domain\Material\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'material_price')]
#[ORM\UniqueConstraint(
    name: 'unique_material_thickness',
    columns: ['material_id', 'thickness']
)]
#[UniqueEntity(
    fields: ['material', 'thickness'],
    message: 'unique',
)]
class MaterialPrice
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $thickness;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: false)]
    private string $price;

    #[ORM\ManyToOne(targetEntity: Material::class, inversedBy: 'prices')]
    #[ORM\JoinColumn(nullable: false)]
    private Material $material;

    protected function __construct(Material $material, int $thickness, string $price)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->thickness = $thickness;
        $this->price = $price;
        $this->material = $material;
    }

    public static function create(Material $material, int $thickness, string $price): self
    {
        return new self($material, $thickness, $price);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getThickness(): int
    {
        return $this->thickness;
    }

    public function setThickness(int $thickness): void
    {
        $this->thickness = $thickness;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): void
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
