<?php

declare(strict_types=1);

namespace App\Domain\Wood\Exception;

use App\Domain\Shared\Exception\AlreadyExistsDomainException;

class WoodAlreadyExistsException extends AlreadyExistsDomainException
{
    public static function withName(string $name): self
    {
        return new self(\sprintf("Wood with code '%s' already exists", $name), 'name');
    }
}
