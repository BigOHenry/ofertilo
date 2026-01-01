<?php

declare(strict_types=1);

namespace App\Application\Color\Query\GetColorFormData;

readonly class GetColorFormDataQuery
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
