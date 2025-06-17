<?php

declare(strict_types=1);

namespace App\Domain\Product\Entity;

use App\Domain\Color\Entity\Color;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_color')]
#[ORM\UniqueConstraint(
    name: 'unique_product_color',
    columns: ['product_id', 'color_id']
)]
class ProductColor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productColor')]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: Color::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Color $color;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function __construct(Product $product, Color $color, ?string $description = null)
    {
        $this->product = $product;
        $this->color = $color;
        $this->description = $description;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getColor(): Color
    {
        return $this->color;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
