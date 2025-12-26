<?php

declare(strict_types=1);

namespace App\Domain\Material\Entity;

use App\Domain\Material\ValueObject\MaterialType;
use App\Domain\Material\ValueObject\MeasurementType;
use App\Domain\Wood\Entity\Wood;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PlywoodMaterial extends Material
{
    public static function create(Wood $wood, bool $enabled = true): self
    {
        $material = new self();
        $material->setWood($wood);
        $material->setEnabled($enabled);

        return $material;
    }

    public function getType(): MaterialType
    {
        return MaterialType::PLYWOOD;
    }

    public function getMeasurementType(): MeasurementType
    {
        return MeasurementType::AREA;
    }
}
