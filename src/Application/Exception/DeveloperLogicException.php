<?php

declare(strict_types=1);

namespace App\Application\Exception;

class DeveloperLogicException extends \LogicException
{
    public static function becauseEntityIsNotPersisted(string $entityClass): self
    {
        return new self(\sprintf('%s must be persisted at this point.', $entityClass));
    }

    public static function invalidArguments(string $message): self
    {
        return new self($message);
    }
}
