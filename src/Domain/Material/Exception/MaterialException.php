<?php

declare(strict_types=1);

namespace App\Domain\Material\Exception;

use App\Domain\Shared\Exception\DomainException;

abstract class MaterialException extends DomainException
{
    protected function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
