<?php

namespace App\Domain\Product\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_size')]
class ProductSize
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productSizes')]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\Column(type: 'integer')]
    private int $length;

    #[ORM\Column(type: 'integer')]
    private int $width;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $thickness = null;

    /**
     * @var Collection<int, ProductComponent>
     */
    #[ORM\OneToMany(
        targetEntity: ProductComponent::class,
        mappedBy: 'productSize',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $productComponents;

    protected function __construct(Product $product, int $length, int $width, ?int $thickness = null)
    {
        $this->product = $product;
        $this->length = $length;
        $this->width = $width;
        $this->thickness = $thickness;
        $this->productComponents = new ArrayCollection();
    }

    public static function create(Product $product, int $length, int $width, ?int $thickness = null): self
    {
        return new self($product, $length, $width, $thickness);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getThickness(): ?int
    {
        return $this->thickness;
    }

    public function setLength(int $length): self
    {
        $this->length = $length;
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
        $dimensions = "{$this->length}×{$this->width}";
        if ($this->thickness !== null) {
            $dimensions .= "×{$this->thickness}";
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
