<?php

declare(strict_types=1);

namespace App\Application\Query\Product\GetProductFormData;

readonly class GetProductFormDataQuery
{
    public function __construct(
        private int $id,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }
}
