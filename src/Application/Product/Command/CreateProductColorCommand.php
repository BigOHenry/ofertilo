<?php

declare(strict_types=1);

namespace App\Application\Product\Command;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Entity\Product;
use Symfony\Component\Validator\Constraints as Assert;

class CreateProductColorCommand
{
    private Product $product;

    #[Assert\NotNull(message: 'not_null')]
    private ?Color $color = null;

    #[Assert\Length(
        max: 500,
        maxMessage: 'max_length'
    )]
    private ?string $description = null;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(?Color $color): void
    {
        $this->color = $color;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
