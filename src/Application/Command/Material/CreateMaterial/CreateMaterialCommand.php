<?php

declare(strict_types=1);

namespace App\Application\Command\Material\CreateMaterial;

use App\Domain\Material\ValueObject\MaterialType;
use Symfony\Component\Form\FormInterface;

final readonly class CreateMaterialCommand
{
    public function __construct(private int $woodId, private MaterialType $type)
    {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self($data['wood']->getId(), $data['type']);
    }

    public function getWoodId(): int
    {
        return $this->woodId;
    }

    public function getType(): MaterialType
    {
        return $this->type;
    }
}
