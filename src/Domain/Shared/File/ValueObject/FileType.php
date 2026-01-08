<?php

declare(strict_types=1);

namespace App\Domain\Shared\File\ValueObject;

enum FileType: string
{
    case IMAGE = 'image';
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::IMAGE => 'Image',
            self::OTHER => 'Other',
        };
    }
}
