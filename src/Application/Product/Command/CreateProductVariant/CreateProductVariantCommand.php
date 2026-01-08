<?php

declare(strict_types=1);

namespace App\Application\Product\Command\CreateProductVariant;

use App\Domain\Product\Entity\Product;
use Symfony\Component\Form\FormInterface;

final readonly class CreateProductVariantCommand
{
    public function __construct(public string $productId, public int $height, public int $width, public ?int $thickness)
    {
    }

    public static function createFromForm(FormInterface $form, Product $product): self
    {
        $data = $form->getData();

        return new self($product->getId(), $data['height'], $data['width'], $data['thickness']);
    }
}
