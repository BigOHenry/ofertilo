<?php

namespace App\Domain\Material;

enum Type: string
{
    case PIECE = 'piece';
    case AREA = 'area';
    case VOLUME = 'volume';

    public function label(): string
    {
        return 'material.type.' . $this->value;
    }
}