<?php

declare(strict_types=1);

namespace App\Domain\Shared\Service;

use App\Domain\Shared\Entity\Country;
use App\Domain\Shared\Repository\CountryRepositoryInterface;

readonly class CountryService
{
    public function __construct(
        private CountryRepositoryInterface $countryRepository,
    ) {
    }

    public function getEnabledCountryByAlpha2(string $alpha2): Country
    {
        $country = $this->countryRepository->findByAlpha2($alpha2);

        if (!$country) {
            throw new \InvalidArgumentException("Country with code '{$alpha2}' not found");
        }

        if (!$country->isEnabled()) {
            throw new \InvalidArgumentException("Country '{$alpha2}' is not enabled");
        }

        return $country;
    }

    public function getEnabledCountryByAlpha3(string $alpha3): Country
    {
        $country = $this->countryRepository->findByAlpha3($alpha3);

        if (!$country) {
            throw new \InvalidArgumentException("Country with code '{$alpha3}' not found");
        }

        if (!$country->isEnabled()) {
            throw new \InvalidArgumentException("Country '{$alpha3}' is not enabled");
        }

        return $country;
    }
}
