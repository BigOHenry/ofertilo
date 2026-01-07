<?php

declare(strict_types=1);

namespace App\Domain\Material\Exception;

use App\Domain\Material\ValueObject\MaterialType;
use App\Domain\Wood\Entity\Wood;

class MaterialNotFoundException extends MaterialException
{
    public static function withCode(Wood $wood, MaterialType $type): self
    {
        return new self(\sprintf("Material with Wood '%s' and Type '%s' not found!", $wood->getName(), $type->value));
    }

    public static function withId(string $id): self
    {
        return new self(\sprintf("Material with id '%s' not found!", $id));
    }
}
