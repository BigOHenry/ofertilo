<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObject;

enum Type: string
{
    case FLAG = 'flag';
    case COAT_OF_ARMS = 'coat_of_arms';

    public function label(): string
    {
        return 'product.type.' . $this->value;
    }
}
