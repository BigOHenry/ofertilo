<?php

declare(strict_types=1);

namespace App\Domain\Shared\Country\Repository;

use App\Domain\Shared\Country\Entity\Country;

interface CountryRepositoryInterface
{
    public function findById(string $id): ?Country;

    public function findByAlpha2(string $alpha2): ?Country;

    public function findByAlpha3(string $alpha3): ?Country;

    /**
     * @return array<string, string>
     */
    public function findAllAsChoices(): array;

    /**
     * @return array<string, string>
     */
    public function findAllAsApiChoices(): array;
}
