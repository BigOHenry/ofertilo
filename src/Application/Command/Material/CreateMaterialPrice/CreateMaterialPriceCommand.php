<?php

declare(strict_types=1);

namespace App\Application\Command\Material\CreateMaterialPrice;

use App\Domain\Material\Entity\Material;
use Symfony\Component\Form\FormInterface;

final readonly class CreateMaterialPriceCommand
{
    public function __construct(private int $materialId, private int $thickness, private string $price)
    {
    }

    public static function createFromForm(FormInterface $form, Material $material): self
    {
        $data = $form->getData();
        \assert($material->getId() !== null, 'Product must have an ID when loaded from database');

        return new self($material->getId(), $data['thickness'], $data['price']);
    }

    public function getMaterialId(): int
    {
        return $this->materialId;
    }

    public function getThickness(): int
    {
        return $this->thickness;
    }

    public function getPrice(): string
    {
        return $this->price;
    }
}
