<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

class InvalidEmailException extends UserException
{
    public static function emptyEmail(): self
    {
        return new self('User e-mail cannot be empty');
    }
}
