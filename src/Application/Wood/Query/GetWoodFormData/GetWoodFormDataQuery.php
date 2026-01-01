<?php

declare(strict_types=1);

namespace App\Application\Wood\Query\GetWoodFormData;

readonly class GetWoodFormDataQuery
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
