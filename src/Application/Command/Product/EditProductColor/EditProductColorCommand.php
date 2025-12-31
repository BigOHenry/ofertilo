<?php

declare(strict_types=1);

namespace App\Application\Command\Product\EditProductColor;

use App\Application\Exception\DeveloperLogicException;
use App\Domain\Product\Entity\Product;
use Symfony\Component\Form\FormInterface;

final readonly class EditProductColorCommand
{
    public function __construct(public int $productId, public int $productColorId, public int $colorId, public string $description)
    {
    }

    public static function createFromForm(FormInterface $form, Product $product): self
    {
        $data = $form->getData();

        $productId = $product->getId();
        if ($productId === null) {
            throw DeveloperLogicException::becauseEntityIsNotPersisted(Product::class);
        }

        return new self($productId, (int) $data['id'], $data['color']->getId(), $data['description']);
    }
}
