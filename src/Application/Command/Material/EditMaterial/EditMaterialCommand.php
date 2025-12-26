<?php

declare(strict_types=1);

namespace App\Application\Command\Material\EditMaterial;

use Symfony\Component\Form\FormInterface;

final readonly class EditMaterialCommand
{
    public function __construct(private int $id, private int $woodId, private bool $enabled = true)
    {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self((int) $data['id'], $data['wood']->getId(), $data['enabled']);
    }

    public function getWoodId(): int
    {
        return $this->woodId;
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
