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
    #[Assert\NotNull(message: 'not_null')]
    #[Assert\Range(
        notInRangeMessage: 'range',
        min: 1,
        max: 100
    )]
    private int $thickness;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: false)]
    #[Assert\NotNull(message: 'not_null')]
    #[Assert\Range(
        notInRangeMessage: 'range',
        min: 1.00,
        max: 999999.99
    )]
    private string $price;

    #[ORM\ManyToOne(targetEntity: Material::class, inversedBy: 'prices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'not_null')]
    private Material $material;

    protected function __construct(Material $material, ?int $id = null)
    {
        $this->id = $id;
        $this->material = $material;
    }

    public static function create(Material $material, int $thickness, string $price): self
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

    public static function createFromDatabase(
        int $id,
        Material $material,
        int $thickness,
        string $price,
    ): self {
        $materialPrice = new self($material, $id);
        $materialPrice->thickness = $thickness;
        $materialPrice->price = $price;

        return $materialPrice;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): void
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

    protected function setId(?int $id = null): void
    {
        $this->id = $id;
    }

    private static function validatePrice(string $price): void
    {
        if (!is_numeric($price)) {
            throw InvalidMaterialPriceException::invalidPriceFormat($price);
        }

        if (!preg_match('/^\d{1,7}(\.\d{1,2})?$/', $price)) {
            throw InvalidMaterialPriceException::invalidPriceFormat($price);
        }

        if (bccomp($price, '1.00', 2) < 0) {
            throw InvalidMaterialPriceException::priceTooLow($price, 1);
        }

        if (bccomp($price, '999999.99', 2) > 0) {
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
