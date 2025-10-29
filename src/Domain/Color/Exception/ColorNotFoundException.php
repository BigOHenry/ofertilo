<?php

declare(strict_types=1);

namespace App\Domain\Color\Exception;

class ColorNotFoundException extends ColorException
{
    public static function withCode(int $code): self
    {
        return new self(\sprintf("Color with code '%s' not found!", $code));
    }

    public static function withId(int $id): self
    {
        return new self(\sprintf("Color with id '%s' not found!", $id));
    }
}
