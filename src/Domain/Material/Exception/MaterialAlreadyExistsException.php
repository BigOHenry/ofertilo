<?php

declare(strict_types=1);

namespace App\Domain\Material\Exception;

use App\Domain\Material\ValueObject\Type;
use App\Domain\Wood\Entity\Wood;

class MaterialAlreadyExistsException extends MaterialException
{
    public static function withWoodAndType(Wood $wood, Type $type): self
    {
        return new self(\sprintf("Material with Wood '%s' and Type '%s' already exists", $wood->getName(), $type->value));
    }
}
