<?php

declare(strict_types=1);

namespace App\Application\Command\Material\EditMaterialPrice;

use Symfony\Component\Form\FormInterface;

final readonly class EditMaterialPriceCommand
{
    public function __construct(private int $id, private int $thickness, private string $price)
    {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self((int) $data['id'], $data['thickness'], $data['price']);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getThickness(): int
    {
        return $this->thickness;
    }

    public function getPrice(): string
    {
        return $this->price;
    }
}
