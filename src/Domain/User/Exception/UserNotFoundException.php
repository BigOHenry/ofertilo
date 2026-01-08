<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

class UserNotFoundException extends UserException
{
    public static function withEmail(string $email): self
    {
        return new self(\sprintf("User with email '%s' not found!", $email));
    }

    public static function withId(string $id): self
    {
        return new self(\sprintf("User with  id '%s' not found!", $id));
    }
}
