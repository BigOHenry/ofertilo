<?php

declare(strict_types=1);

namespace App\Domain\Shared\Repository;

use App\Domain\Shared\Entity\Country;

interface CountryRepositoryInterface
{
    public function findById(int $id): ?Country;

    public function findByAlpha2(string $alpha2): ?Country;

    public function findByAlpha3(string $alpha3): ?Country;

    /**
     * @return array<string, int>
     */
    public function findAllAsChoices(): array;

    /**
     * @return array<string, string>
     */
    public function findAllAsApiChoices(): array;
}
