<?php

declare(strict_types=1);

namespace App\Application\Material\Command\CreateMaterialPrice;

use App\Domain\Material\Entity\Material;
use Symfony\Component\Form\FormInterface;

final readonly class CreateMaterialPriceCommand
{
    public function __construct(public string $materialId, public int $thickness, public string $price)
    {
    }

    public static function createFromForm(FormInterface $form, Material $material): self
    {
        $data = $form->getData();

        return new self($material->getId(), $data['thickness'], $data['price']);
    }
}
