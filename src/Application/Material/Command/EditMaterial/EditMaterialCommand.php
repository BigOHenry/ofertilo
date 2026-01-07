<?php

declare(strict_types=1);

namespace App\Application\Material\Command\EditMaterial;

use Symfony\Component\Form\FormInterface;

final readonly class EditMaterialCommand
{
    public function __construct(public string $materialId, public string $woodId, public bool $enabled = true)
    {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self($data['id'], $data['wood']->getId(), $data['enabled']);
    }
}
