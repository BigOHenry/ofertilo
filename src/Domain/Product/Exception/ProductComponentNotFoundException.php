<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

class ProductComponentNotFoundException extends ProductException
{
    public static function withId(string $id): self
    {
        return new self(\sprintf("ProductComponent '%s' not found", $id));
    }
}
