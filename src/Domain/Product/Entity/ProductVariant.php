<?php

declare(strict_types=1);

namespace App\Domain\Product\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'product_variant')]
class ProductVariant
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productVariants')]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\Column(type: 'integer')]
    private int $height;

    #[ORM\Column(type: 'integer')]
    private int $width;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $thickness = null;

    /**
     * @var Collection<int, ProductComponent>
     */
    #[ORM\OneToMany(
        targetEntity: ProductComponent::class,
        mappedBy: 'productVariant',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $productComponents;

    protected function __construct(Product $product, int $height, int $width, ?int $thickness = null)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->product = $product;
        $this->height = $height;
        $this->width = $width;
        $this->thickness = $thickness;
        $this->productComponents = new ArrayCollection();
    }

    public static function create(Product $product, int $height, int $width, ?int $thickness = null): self
    {
        return new self($product, $height, $width, $thickness);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getThickness(): ?int
    {
        return $this->thickness;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function setThickness(?int $thickness): self
    {
        $this->thickness = $thickness;

        return $this;
    }

    public function getDimensionsString(): string
    {
        $dimensions = \sprintf('%s×%s', $this->height, $this->width);
        if ($this->thickness !== null) {
            $dimensions .= \sprintf('×%s', $this->thickness);
        }

        return $dimensions . ' mm';
    }

    /**
     * @return Collection<int, ProductComponent>
     */
    public function getProductComponents(): Collection
    {
        return $this->productComponents;
    }

    public function addProductComponent(ProductComponent $productComponent): self
    {
        if (!$this->productComponents->contains($productComponent)) {
            $this->productComponents->add($productComponent);
        }

        return $this;
    }

    public function removeProductComponent(ProductComponent $productComponent): self
    {
        $this->productComponents->removeElement($productComponent);

        return $this;
    }
}
