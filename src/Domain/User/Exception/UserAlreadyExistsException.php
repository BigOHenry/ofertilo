<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

class UserAlreadyExistsException extends UserException
{
    public static function withEmail(): self
    {
        return new self('User with this email already exists');
    }
}
