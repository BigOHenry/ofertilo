<?php

declare(strict_types=1);

namespace App\Domain\Shared\File\Exception;


class FileNotFoundException extends FileException
{
    public static function withId(string $id): self
    {
        return new self(\sprintf("File with id '%s' not found!", $id));
    }
}
