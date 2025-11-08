<?php

declare(strict_types=1);

namespace App\Application\Command\Material\EditMaterial;

use App\Domain\Material\ValueObject\Type;
use App\Domain\Wood\Entity\Wood;
use Symfony\Component\Form\FormInterface;

final readonly class EditMaterialCommand
{
    public function __construct(private int $id, private Wood $wood, private Type $type, private bool $enabled = true)
    {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self((int) $data['id'], $data['wood'], Type::from($data['type']), $data['enabled']);
    }

    public function getWood(): Wood
    {
        return $this->wood;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
