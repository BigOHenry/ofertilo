<?php

declare(strict_types=1);

namespace App\Domain\Wood\Exception;

class WoodNotFoundException extends WoodException
{
    public static function withName(string $name): self
    {
        return new self(\sprintf("Wood with name '%s' not found!", $name));
    }

    public static function withId(int $id): self
    {
        return new self(\sprintf("Wood with id '%s' not found!", $id));
    }
}
