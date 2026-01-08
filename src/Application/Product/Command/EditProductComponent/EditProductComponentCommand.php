<?php

declare(strict_types=1);

namespace App\Application\Product\Command\EditProductComponent;

use App\Domain\Product\Entity\ProductComponent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class EditProductComponentCommand
{
    public function __construct(
        public string $productComponentId,
        public int $quantity,
        public int $length,
        public int $width,
        public int $thickness,
        public ?string $shapeDescription = null,
        public ?UploadedFile $blueprintFile = null,
    ) {
    }

    public static function createFromForm(FormInterface $form, ProductComponent $productComponent): self
    {
        $data = $form->getData();

        return new self(
            $productComponent->getId(),
            $data['quantity'],
            $data['length'],
            $data['width'],
            $data['thickness'],
            $data['shapeDescription'],
            $data['blueprintFile']
        );
    }
}
