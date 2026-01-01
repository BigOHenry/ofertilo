<?php

declare(strict_types=1);

namespace App\Application\Material\Query\CalculateMaterialPricePerUnit;

use App\Application\Shared\Exception\DeveloperLogicException;
use App\Domain\Material\Entity\Material;
use Symfony\Component\Form\FormInterface;

readonly class CalculateMaterialPricePerUnitQuery
{
    public function __construct(
        private int $materialId,
        private int $length,
        private int $width,
        private ?int $thickness,
        private float $price,
    ) {
    }

    public static function createFromForm(FormInterface $form, Material $material): self
    {
        $data = $form->getData();

        $materialId = $material->getId();
        if ($materialId === null) {
            throw DeveloperLogicException::becauseEntityIsNotPersisted(Material::class);
        }

        return new self($materialId, $data['length'], $data['width'], $data['thickness'], (float) $data['price']);
    }

    public function getMaterialId(): int
    {
        return $this->materialId;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getThickness(): ?int
    {
        return $this->thickness;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}
