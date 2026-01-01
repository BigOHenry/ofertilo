<?php

declare(strict_types=1);

namespace App\Application\Material\Query\GetMaterialFormData;

readonly class GetMaterialFormDataQuery
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
