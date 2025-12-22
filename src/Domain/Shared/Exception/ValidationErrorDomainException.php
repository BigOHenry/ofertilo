<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception;

class ValidationErrorDomainException extends DomainException
{
    /**
     * @param array<string, array{key: string, params?: array<string, string|int|float|null>}> $errors
     */
    final public function __construct(string $message, private array $errors = [], int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct(message: $message, code: $code, previous: $previous);
    }

    /**
     * @return array<string, array{key: string, params?: array<string, string|int|float|null>}>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * @param array<string, array{key: string, params?: array<string, string|int|float|null>}> $errors
     */
    public static function withErrors(array $errors): static
    {
        return new static('Invalid data', $errors);
    }
}
