<?php

declare(strict_types=1);

namespace App\Application\Command\Product\CreateProductColor;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Entity\Product;
use Symfony\Component\Form\FormInterface;

final readonly class CreateProductColorCommand
{
    public function __construct(private Product $product, private Color $color, private ?string $description)
    {
    }

    public static function createFromForm(FormInterface $form, Product $product): self
    {
        $data = $form->getData();

        return new self($product, $data['color'], $data['description']);
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
}
