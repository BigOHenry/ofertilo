<?php

declare(strict_types=1);

namespace App\Domain\Material\Entity;

use App\Domain\Material\Exception\InvalidMaterialPriceException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotNull]
    #[Assert\Range(notInRangeMessage: 'Thickness must be between {{ min }}mm and {{ max }}mm', min: 1, max: 100)]
    private int $thickness;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: false)]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'Price must be positive')]
    #[Assert\Range(min: 1.00, max: 999999.99)]
    private float $price;

    #[ORM\ManyToOne(targetEntity: Material::class, inversedBy: 'prices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private Material $material;

    protected function __construct(Material $material)
    {
        $this->material = $material;
    }

    public static function create(Material $material, int $thickness, float $price): self
    {
        self::validateThickness($thickness);
        self::validatePrice($price);

        $product = new self($material);
        $product->thickness = $thickness;
        $product->price = $price;
        return $product;
    }

    public static function createEmpty(Material $material): self
    {
        return new self($material);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getThickness(): int
    {
        return $this->thickness;
    }

    public function setThickness(int $thickness): void
    {
        self::validateThickness($thickness);
        $this->thickness = $thickness;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        self::validatePrice($price);
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

    private static function validatePrice(float $price): void
    {
        if ($price < 1) {
            throw InvalidMaterialPriceException::priceTooLow($price, 1);
        }

        if ($price > 999999.99) {
            throw InvalidMaterialPriceException::priceTooHigh($price, 999999.99);
        }
    }

    private static function validateThickness(int $thickness): void
    {
        if ($thickness < 1) {
            throw InvalidMaterialPriceException::thicknessTooLow($thickness, 1);
        }

        if ($thickness > 100) {
            throw InvalidMaterialPriceException::thicknessTooHigh($thickness, 100);
        }
    }
}
