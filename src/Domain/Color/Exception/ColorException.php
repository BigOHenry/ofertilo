<?php

declare(strict_types=1);

namespace App\Domain\Color\Exception;

abstract class ColorException extends \DomainException
{
    protected function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
