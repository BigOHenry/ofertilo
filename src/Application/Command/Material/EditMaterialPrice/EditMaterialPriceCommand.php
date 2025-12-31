<?php

declare(strict_types=1);

namespace App\Application\Command\Material\EditMaterialPrice;

use App\Application\Exception\DeveloperLogicException;
use App\Domain\Material\Entity\Material;
use Symfony\Component\Form\FormInterface;

final readonly class EditMaterialPriceCommand
{
    public function __construct(public int $materialId, public int $priceId, public int $thickness, public string $price)
    {
    }

    public static function createFromForm(FormInterface $form, Material $material): self
    {
        $data = $form->getData();
        $materialId = $material->getId();

        if ($materialId === null) {
            throw DeveloperLogicException::becauseEntityIsNotPersisted(Material::class);
        }

        return new self($materialId, (int) $data['id'], $data['thickness'], $data['price']);
    }
}
