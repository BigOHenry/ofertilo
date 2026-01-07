<?php

declare(strict_types=1);

namespace App\Domain\Product\Entity;

use App\Domain\Color\Entity\Color;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'product_color')]
#[ORM\UniqueConstraint(
    name: 'unique_product_color',
    columns: ['product_id', 'color_id']
)]
#[UniqueEntity(
    fields: ['product', 'color'],
    message: 'unique',
)]
class ProductColor
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productColors')]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: Color::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Color $color;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $description = null;

    protected function __construct(Product $product)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->product = $product;
    }

    public static function create(Product $product, Color $color, ?string $description = null): self
    {
        $productColor = new self($product);
        $productColor->color = $color;
        $productColor->description = $description;

        return $productColor;
    }

    public function getId(): string
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

    public function setDescription(?string $description = null): self
    {
        $this->description = $description;

        return $this;
    }

    public function setColor(Color $color): void
    {
        $this->color = $color;
    }
}
