<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\Exception\AlreadyExistsDomainException;

class UserAlreadyExistsException extends AlreadyExistsDomainException
{
    public static function withEmail(): self
    {
        return new self('User with this email already exists', 'email');
    }
}
