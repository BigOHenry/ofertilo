<?php

declare(strict_types=1);

namespace App\Domain\Color\Exception;

use App\Domain\Shared\Exception\AlreadyExistsDomainException;

class ColorAlreadyExistsException extends AlreadyExistsDomainException
{
    public static function withCode(int $code): self
    {
        return new self(\sprintf("Color with code '%s' already exists", $code), 'code');
    }
}
