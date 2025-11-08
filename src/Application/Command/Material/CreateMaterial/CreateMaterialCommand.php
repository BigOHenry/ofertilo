<?php

declare(strict_types=1);

namespace App\Application\Command\Material\CreateMaterial;

use App\Domain\Material\ValueObject\Type;
use App\Domain\Wood\Entity\Wood;
use Symfony\Component\Form\FormInterface;

final readonly class CreateMaterialCommand
{
    public function __construct(private Wood $wood, private Type $type)
    {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self($data['wood'], $data['type']);
    }


    public function getWood(): Wood
    {
        return $this->wood;
    }

    public function getType(): Type
    {
        return $this->type;
    }
}
