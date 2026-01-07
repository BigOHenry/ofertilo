<?php

declare(strict_types=1);

namespace App\Domain\Product\Entity;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\ValueObject\MeasurementType;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity]
#[ORM\Table(name: 'product_component')]
class ProductComponent
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: ProductSize::class, inversedBy: 'productComponents')]
    #[ORM\JoinColumn(nullable: false)]
    private ProductSize $productSize;

    #[ORM\ManyToOne(targetEntity: Material::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Material $material;

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

    /**
     * @var resource|string|null
     */
    #[ORM\Column(type: 'blob', nullable: true)]
    private $blueprintImage;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $blueprintOriginalName = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $blueprintMimeType = null;

    private ?UploadedFile $blueprintFile = null;

    private function __construct(
        ProductSize $productSize,
        Material $material,
        int $quantity,
        int $length,
        int $width,
        int $thickness,
        ?string $shapeDescription = null,
    ) {
        $this->id = Uuid::uuid4()->toString();

        $this->productSize = $productSize;
        $this->material = $material;
        $this->quantity = $quantity;
        $this->length = $length;
        $this->width = $width;
        $this->thickness = $thickness;
        $this->shapeDescription = $shapeDescription;
    }

    public static function create(
        ProductSize $productSize,
        Material $material,
        int $quantity,
        int $length,
        int $width,
        int $thickness,
        ?string $shapeDescription = null,
    ): self {
        $component = new self($productSize, $material, $quantity, $length, $width, $thickness, $shapeDescription);
        $productSize->addProductComponent($component);

        return $component;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProductSize(): ProductSize
    {
        return $this->productSize;
    }

    public function setProductSize(ProductSize $productSize): self
    {
        $this->productSize = $productSize;

        return $this;
    }

    public function getMaterial(): ?Material
    {
        return $this->material;
    }

    public function setMaterial(?Material $material): self
    {
        $this->material = $material;

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

    public function getThickness(): ?int
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

    public function getBlueprintImage(): ?string
    {
        if ($this->blueprintImage === null) {
            return null;
        }

        if (\is_resource($this->blueprintImage)) {
            $content = stream_get_contents($this->blueprintImage, -1, 0);

            return $content !== false ? $content : null;
        }

        \assert(\is_string($this->blueprintImage));

        return $this->blueprintImage;
    }

    public function setBlueprintImage(string $blueprintImage): self
    {
        $this->blueprintImage = $blueprintImage;

        return $this;
    }

    public function hasBlueprint(): bool
    {
        return $this->blueprintImage !== null;
    }

    public function removeBlueprint(): self
    {
        $this->blueprintImage = null;
        $this->blueprintOriginalName = null;
        $this->blueprintMimeType = null;

        return $this;
    }

    public function getBlueprintOriginalName(): ?string
    {
        return $this->blueprintOriginalName;
    }

    public function setBlueprintOriginalName(?string $blueprintOriginalName): self
    {
        $this->blueprintOriginalName = $blueprintOriginalName;

        return $this;
    }

    public function getBlueprintMimeType(): ?string
    {
        return $this->blueprintMimeType;
    }

    public function setBlueprintMimeType(?string $blueprintMimeType): self
    {
        $this->blueprintMimeType = $blueprintMimeType;

        return $this;
    }

    public function getBlueprintFile(): ?UploadedFile
    {
        return $this->blueprintFile;
    }

    public function setBlueprintFile(?UploadedFile $blueprintFile): self
    {
        $this->blueprintFile = $blueprintFile;
        if ($blueprintFile) {
            $this->setBlueprintOriginalName($blueprintFile->getClientOriginalName());
            $this->setBlueprintMimeType($blueprintFile->getMimeType());
        }

        return $this;
    }

    public function getBlueprintAsBase64(): ?string
    {
        $imageData = $this->getBlueprintImage();
        if ($imageData === null) {
            return null;
        }

        return base64_encode($imageData);
    }

    public function getBlueprintDataUri(): ?string
    {
        if (!$this->hasBlueprint()) {
            return null;
        }
        $base64 = $this->getBlueprintAsBase64();
        $mimeType = $this->blueprintMimeType ?? 'image/jpeg';

        return \sprintf('data:%s;base64,%s', $mimeType, $base64);
    }

    public function hasComplexShape(): bool
    {
        return $this->shapeDescription !== null || $this->blueprintImage !== null;
    }

    public function getDimensionsString(): string
    {
        $dimensions = \sprintf('%s×%s×%s', $this->length, $this->width, $this->thickness);

        return $dimensions . ' mm';
    }

    /**
     * Returns a description of the component, including the material.
     */
    public function getFullDescription(?string $locale = null): string
    {
        $parts = [];
        $parts[] = $this->material?->getDescription($locale);
        $parts[] = $this->getDimensionsString();
        $parts[] = \sprintf('(%s ks)', $this->quantity);

        return implode(' ', $parts);
    }

    /**
     * Calculates volume/area according to material type.
     */
    public function calculateMaterialAmount(): ?float
    {
        return match ($this->material?->getMeasurementType()) {
            MeasurementType::VOLUME => $this->calculateVolume(),
            MeasurementType::AREA => $this->calculateArea(),
            MeasurementType::PIECE => (float) $this->quantity,
            default => null,
        };
    }

    private function calculateVolume(): ?float
    {
        if (!$this->length || !$this->width || !$this->thickness) {
            return null;
        }

        // Calculation in m³
        return ($this->length / 1000) * ($this->width / 1000) * ($this->thickness / 1000) * $this->quantity;
    }

    private function calculateArea(): ?float
    {
        if (!$this->length || !$this->width) {
            return null;
        }

        // Calculation in m²
        return ($this->length / 1000) * ($this->width / 1000) * $this->quantity;
    }
}
