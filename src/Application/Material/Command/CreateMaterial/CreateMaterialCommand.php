<?php

declare(strict_types=1);

namespace App\Application\Material\Command\CreateMaterial;

use App\Domain\Material\ValueObject\MaterialType;
use Symfony\Component\Form\FormInterface;

final readonly class CreateMaterialCommand
{
    public function __construct(public string $woodId, public MaterialType $type)
    {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self($data['wood']->getId(), $data['type']);
    }
}
