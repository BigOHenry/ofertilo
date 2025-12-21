<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception;

class ValidationErrorDomainException extends DomainException
{
    /**
     * @param array<string, array{key: string, params?: array<string>}|string> $errors
     */
    final private function __construct(private array $errors = [], int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct(message: 'Invalid data', code: $code, previous: $previous);
    }

    /**
     * @return array<string, array{key: string, params?: array<string>}|string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    /**
     * @param array<string, array{key: string, params?: array<string>}|string> $errors
     */
    public static function withErrors(array $errors): static
    {
        return new static($errors);
    }
}
