<?php

declare(strict_types=1);

namespace App\Domain\Product\Entity;

use App\Domain\Shared\File\Entity\File;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'product_component')]
class ProductComponent
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: ProductVariant::class, inversedBy: 'productComponents')]
    #[ORM\JoinColumn(nullable: false)]
    private ProductVariant $productVariant;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $length;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $width;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $thickness;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $shapeDescription = null;

    #[ORM\OneToOne(targetEntity: File::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'blueprint_file_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?File $blueprintFile = null;

    private function __construct(
        ProductVariant $productVariant,
        int $quantity,
        int $length,
        int $width,
        int $thickness,
        ?string $shapeDescription = null,
    ) {
        $this->id = Uuid::uuid4()->toString();
        $this->productVariant = $productVariant;
        $this->quantity = $quantity;
        $this->length = $length;
        $this->width = $width;
        $this->thickness = $thickness;
        $this->shapeDescription = $shapeDescription;
    }

    public static function create(
        ProductVariant $productVariant,
        int $quantity,
        int $length,
        int $width,
        int $thickness,
        ?string $shapeDescription = null,
    ): self {
        $component = new self($productVariant, $quantity, $length, $width, $thickness, $shapeDescription);
        $productVariant->addProductComponent($component);

        return $component;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProductVariant(): ProductVariant
    {
        return $this->productVariant;
    }

    public function setProductVariant(ProductVariant $productVariant): self
    {
        $this->productVariant = $productVariant;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getThickness(): int
    {
        return $this->thickness;
    }

    public function setThickness(int $thickness): self
    {
        $this->thickness = $thickness;

        return $this;
    }

    public function getShapeDescription(): ?string
    {
        return $this->shapeDescription;
    }

    public function setShapeDescription(?string $shapeDescription): self
    {
        $this->shapeDescription = $shapeDescription;

        return $this;
    }

    public function getBlueprintFile(): ?File
    {
        return $this->blueprintFile;
    }

    public function setBlueprintFile(?File $blueprintFile): self
    {
        $this->blueprintFile = $blueprintFile;

        return $this;
    }

    public function hasBlueprint(): bool
    {
        return $this->blueprintFile !== null;
    }

    public function removeBlueprint(): self
    {
        $this->blueprintFile = null;

        return $this;
    }

    public function hasComplexShape(): bool
    {
        return $this->shapeDescription !== null || $this->blueprintFile !== null;
    }

    public function getDimensionsString(): string
    {
        return \sprintf('%s×%s×%s mm', $this->length, $this->width, $this->thickness);
    }

    public function getFullDescription(?string $locale = null): string
    {
        $parts = [];
        $parts[] = $this->getDimensionsString();
        $parts[] = \sprintf('(%s ks)', $this->quantity);

        return implode(' ', $parts);
    }

    /* @phpstan-ignore-next-line */
    private function calculateVolume(): ?float
    {
        if (!$this->length || !$this->width || !$this->thickness) {
            return null;
        }

        // Calculation in m³
        return ($this->length / 1000) * ($this->width / 1000) * ($this->thickness / 1000) * $this->quantity;
    }

    /* @phpstan-ignore-next-line */
    private function calculateArea(): ?float
    {
        if (!$this->length || !$this->width) {
            return null;
        }

        // Calculation in m²
        return ($this->length / 1000) * ($this->width / 1000) * $this->quantity;
    }
}
