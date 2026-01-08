<?php

declare(strict_types=1);

namespace App\Domain\Shared\File\Exception;

class FileErrorException extends FileException
{
    public static function failToUpload(): self
    {
        return new self('Failed to upload file.');
    }

    public static function failToCreateOrFindFolder(string $path): self
    {
        return new self(\sprintf('Failed to create or find folder: %s.', $path));
    }
}
