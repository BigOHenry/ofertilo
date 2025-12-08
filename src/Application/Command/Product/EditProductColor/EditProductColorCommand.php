<?php

declare(strict_types=1);

namespace App\Application\Command\Product\EditProductColor;

use Symfony\Component\Form\FormInterface;

final readonly class EditProductColorCommand
{
    public function __construct(private int $id, private int $colorId, private string $description)
    {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self((int) $data['id'], $data['color']->getId(), $data['description']);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getColorId(): int
    {
        return $this->colorId;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
