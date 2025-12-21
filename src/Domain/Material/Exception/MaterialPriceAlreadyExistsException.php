<?php

declare(strict_types=1);

namespace App\Domain\Material\Exception;

use App\Domain\Shared\Exception\AlreadyExistsDomainException;

class MaterialPriceAlreadyExistsException extends AlreadyExistsDomainException
{
    public static function withThickness(int $thickness): self
    {
        return new self(\sprintf('Price for thickness %s mm already exists', $thickness), 'thickness');
    }
}
