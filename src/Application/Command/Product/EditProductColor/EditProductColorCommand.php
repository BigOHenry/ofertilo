<?php

declare(strict_types=1);

namespace App\Application\Command\Product\EditProductColor;

use App\Domain\Color\Entity\Color;
use Symfony\Component\Form\FormInterface;

final readonly class EditProductColorCommand
{
    public function __construct(private int $id, private Color $color, private string $description)
    {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self((int) $data['id'], $data['color'], $data['description']);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getColor(): Color
    {
        return $this->color;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
