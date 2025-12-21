<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception;

class AlreadyExistsDomainException extends DomainException
{
    final public function __construct(string $message, private readonly string $field, int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct(message: $message, code: $code, previous: $previous);
    }

    public function getField(): string
    {
        return $this->field;
    }

    protected static function withField(string $message, string $field): static
    {
        return new static($message, $field);
    }
}
