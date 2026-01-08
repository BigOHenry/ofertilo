<?php

declare(strict_types=1);

namespace App\Application\Product\Command\EditProductVariant;

use App\Domain\Product\Entity\Product;
use Symfony\Component\Form\FormInterface;

final readonly class EditProductVariantCommand
{
    public function __construct(
        public string $productId,
        public string $productVariantId,
        public int $height,
        public int $width,
        public ?int $thickness,
    ) {
    }

    public static function createFromForm(FormInterface $form, Product $product): self
    {
        $data = $form->getData();

        return new self($product->getId(), $data['id'], $data['height'], $data['width'], $data['thickness']);
    }
}
