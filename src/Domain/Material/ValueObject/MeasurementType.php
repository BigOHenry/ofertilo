<?php

declare(strict_types=1);

namespace App\Domain\Material\ValueObject;

enum MeasurementType: string
{
    case AREA = 'area';
    case VOLUME = 'volume';
    case PIECE = 'piece';

    public function getUnit(): string
    {
        return match ($this) {
            self::AREA => 'm²',
            self::VOLUME => 'm³',
            self::PIECE => 'ks',
        };
    }
}
