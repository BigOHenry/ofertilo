<?php

declare(strict_types=1);

namespace App\Application\Product\Command\CreateProductColor;

use App\Domain\Product\Entity\Product;
use Symfony\Component\Form\FormInterface;

final readonly class CreateProductColorCommand
{
    public function __construct(public string $productId, public string $colorId, public ?string $description)
    {
    }

    public static function createFromForm(FormInterface $form, Product $product): self
    {
        $data = $form->getData();

        return new self($product->getId(), $data['color']->getId(), $data['description']);
    }
}
