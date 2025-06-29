<?php

declare(strict_types=1);

namespace App\Domain\Color\Exception;

class ColorAlreadyExistsException extends ColorException
{
    public static function withCode(int $code): self
    {
        return new self("Color with code '{$code}' already exists");
    }
}
