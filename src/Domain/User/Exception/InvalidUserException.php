<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

class InvalidUserException extends UserException
{
    public static function emptyEmail(): self
    {
        return new self('User e-mail cannot be empty');
    }

    public static function invalidEmailFormat(string $email): self
    {
        return new self("Invalid email format: {$email}");
    }

    public static function emptyName(): self
    {
        return new self('User name cannot be empty');
    }
}
