<?php

declare(strict_types=1);

namespace App\Application\Command\Material\CreateMaterialPrice;

use App\Domain\Material\Entity\Material;
use Symfony\Component\Form\FormInterface;

final readonly class CreateMaterialPriceCommand
{
    public function __construct(private Material $material, private int $thickness, private string $price)
    {
    }

    public static function createFromForm(FormInterface $form, Material $material): self
    {
        $data = $form->getData();

        return new self($material, $data['thickness'], $data['price']);
    }

    public function getMaterial(): Material
    {
        return $this->material;
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
