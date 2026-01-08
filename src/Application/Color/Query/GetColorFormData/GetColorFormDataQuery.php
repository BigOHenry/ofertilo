<?php

declare(strict_types=1);

namespace App\Application\Color\Query\GetColorFormData;

final readonly class GetColorFormDataQuery
{
    public function __construct(
        public string $id,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
