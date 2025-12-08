<?php

declare(strict_types=1);

namespace App\Application\Command\Product\CreateProductColor;

use App\Domain\Product\Entity\Product;
use Symfony\Component\Form\FormInterface;

final readonly class CreateProductColorCommand
{
    public function __construct(private int $productId, private int $colorId, private ?string $description)
    {
    }

    public static function createFromForm(FormInterface $form, Product $product): self
    {
        $data = $form->getData();

        return new self($product->getId(), $data['color']->getId(), $data['description']);
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getColorId(): int
    {
        return $this->colorId;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
