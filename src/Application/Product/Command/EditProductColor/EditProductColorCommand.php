<?php

declare(strict_types=1);

namespace App\Application\Product\Command\EditProductColor;

use App\Domain\Product\Entity\Product;
use Symfony\Component\Form\FormInterface;

final readonly class EditProductColorCommand
{
    public function __construct(public string $productId, public string $productColorId, public string $colorId, public string $description)
    {
    }

    public static function createFromForm(FormInterface $form, Product $product): self
    {
        $data = $form->getData();

        return new self($product->getId(), $data['id'], $data['color']->getId(), $data['description']);
    }
}
