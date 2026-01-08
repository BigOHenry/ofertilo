<?php

declare(strict_types=1);

namespace App\Application\Wood\Query\GetWoodFormData;

readonly class GetWoodFormDataQuery
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
