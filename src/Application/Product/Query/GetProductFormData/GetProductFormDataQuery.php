<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductFormData;

readonly class GetProductFormDataQuery
{
    public function __construct(
        private string $id,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
