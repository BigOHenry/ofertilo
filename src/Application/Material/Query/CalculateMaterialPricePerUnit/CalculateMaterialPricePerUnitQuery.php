<?php

declare(strict_types=1);

namespace App\Application\Material\Query\CalculateMaterialPricePerUnit;

use App\Domain\Material\Entity\Material;
use Symfony\Component\Form\FormInterface;

readonly class CalculateMaterialPricePerUnitQuery
{
    public function __construct(
        public string $materialId,
        public int $length,
        public int $width,
        public ?int $thickness,
        public float $price,
    ) {
    }

    public static function createFromForm(FormInterface $form, Material $material): self
    {
        $data = $form->getData();

        return new self($material->getId(), $data['length'], $data['width'], $data['thickness'], (float) $data['price']);
    }
}
