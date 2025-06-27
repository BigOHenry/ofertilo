<?php

declare(strict_types=1);

namespace App\Domain\Material\Exception;

use App\Domain\Material\ValueObject\Type;

class MaterialAlreadyExistsException extends MaterialException
{
    public static function withTypeAndName(Type $type, string $name): self
    {
        return new self("Material with type '{$type->label()}' and name '{$name}' already exists");
    }
}
