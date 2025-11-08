<?php

declare(strict_types=1);

namespace App\Domain\Wood\Exception;

class WoodAlreadyExistsException extends WoodException
{
    public static function withName(string $name): self
    {
        return new self(\sprintf("Wood with code '%s' already exists", $name));
    }
}
