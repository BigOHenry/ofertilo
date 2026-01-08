<?php

declare(strict_types=1);

namespace App\Application\Material\Command\EditMaterialPrice;

use App\Domain\Material\Entity\Material;
use Symfony\Component\Form\FormInterface;

final readonly class EditMaterialPriceCommand
{
    public function __construct(public string $materialId, public string $priceId, public int $thickness, public string $price)
    {
    }

    public static function createFromForm(FormInterface $form, Material $material): self
    {
        $data = $form->getData();

        return new self($material->getId(), $data['id'], $data['thickness'], $data['price']);
    }
}
