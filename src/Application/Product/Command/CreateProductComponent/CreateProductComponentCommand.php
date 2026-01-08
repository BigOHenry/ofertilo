<?php

declare(strict_types=1);

namespace App\Application\Product\Command\CreateProductComponent;

use App\Domain\Product\Entity\ProductVariant;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class CreateProductComponentCommand
{
    public function __construct(
        public string $productVariantId,
        public int $quantity,
        public int $length,
        public int $width,
        public int $thickness,
        public ?string $shapeDescription = null,
        public ?UploadedFile $blueprintFile = null,
    ) {
    }

    public static function createFromForm(FormInterface $form, ProductVariant $productVariant): self
    {
        $data = $form->getData();

        return new self(
            $productVariant->getId(),
            $data['quantity'],
            $data['length'],
            $data['width'],
            $data['thickness'],
            $data['shapeDescription'],
            $data['blueprintFile']
        );
    }
}
