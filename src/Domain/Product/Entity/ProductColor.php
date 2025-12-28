<?php

declare(strict_types=1);

namespace App\Domain\Product\Entity;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Exception\InvalidProductColorException;
use Doctrine\ORM\Mapping as ORM;
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
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productColors')]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: Color::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Color $color;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $description = null;

    protected function __construct(Product $product, ?int $id = null)
    {
        $this->id = $id;
        $this->product = $product;
    }

    public static function create(Product $product, Color $color, ?string $description = null): self
    {
        if ($description !== null) {
            self::validateDescription($description);
        }

        $productColor = new self($product);
        $productColor->color = $color;
        $productColor->description = $description;

        return $productColor;
    }

    public static function createFromDatabase(
        int $id,
        Product $product,
        Color $color,
        ?string $description = null,
    ): self {
        $productColor = new self($product, $id);
        $productColor->color = $color;
        $productColor->description = $description;

        return $productColor;
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

    public function setDescription(?string $description = null): self
    {
        if ($description !== null) {
            self::validateDescription($description);
        }
        $this->description = $description;

        return $this;
    }

    public function setColor(Color $color): void
    {
        $this->color = $color;
    }

    protected function setId(?int $id): void
    {
        $this->id = $id;
    }

    private static function validateDescription(string $description): void
    {
        $trimmed = mb_trim($description);
        if (mb_strlen($trimmed) > 500) {
            throw InvalidProductColorException::descriptionTooLong(500);
        }
    }
}
