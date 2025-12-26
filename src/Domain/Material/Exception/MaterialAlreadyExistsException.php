<?php

declare(strict_types=1);

namespace App\Domain\Material\Exception;

use App\Domain\Material\ValueObject\MaterialType;
use App\Domain\Shared\Exception\AlreadyExistsDomainException;
use App\Domain\Wood\Entity\Wood;

class MaterialAlreadyExistsException extends AlreadyExistsDomainException
{
    public static function withWoodAndType(Wood $wood, MaterialType $type): self
    {
        return new self(\sprintf("Material with Wood '%s' and Type '%s' already exists", $wood->getName(), $type->value), 'wood');
    }
}
