<?php

declare(strict_types=1);

namespace App\Application\Material\Query\GetMaterialFormData;

readonly class GetMaterialFormDataQuery
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
