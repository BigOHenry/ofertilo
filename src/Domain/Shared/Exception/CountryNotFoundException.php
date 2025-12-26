<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception;

class CountryNotFoundException extends CountryException
{
    public static function withAlpha2(string $alpha2): self
    {
        return new self(\sprintf("Country with alpha2 '%s' not found!", $alpha2));
    }

    public static function withAlpha3(string $alpha3): self
    {
        return new self(\sprintf("Country with alpha3 '%s' not found!", $alpha3));
    }

    public static function withAlpha2NotActive(string $alpha2): self
    {
        return new self(\sprintf("Country with alpha2 '%s' not active!", $alpha2));
    }

    public static function withAlpha3NotActive(string $alpha3): self
    {
        return new self(\sprintf("Country with alpha3 '%s' not active!", $alpha3));
    }

    public static function withId(int $id): self
    {
        return new self(\sprintf("Country with id '%s' not found!", $id));
    }

    public static function withIdNotActive(int $id): self
    {
        return new self(\sprintf("Country with id '%s' not active!", $id));
    }
}
