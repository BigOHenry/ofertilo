<?php

declare(strict_types=1);

namespace App\Application\Shared\Country\Service;

use App\Domain\Shared\Country\Entity\Country;
use App\Domain\Shared\Country\Exception\CountryNotFoundException;
use App\Domain\Shared\Country\Repository\CountryRepositoryInterface;

readonly class CountryService
{
    public function __construct(
        private CountryRepositoryInterface $countryRepository,
    ) {
    }

    public function getEnabledCountryById(string $id): Country
    {
        $country = $this->countryRepository->findById($id);

        if (!$country) {
            throw CountryNotFoundException::withId($id);
        }

        if (!$country->isEnabled()) {
            throw CountryNotFoundException::withIdNotActive($id);
        }

        return $country;
    }

    public function getEnabledCountryByAlpha2(string $alpha2): Country
    {
        $country = $this->countryRepository->findByAlpha2($alpha2);

        if (!$country) {
            throw CountryNotFoundException::withAlpha2($alpha2);
        }

        if (!$country->isEnabled()) {
            throw CountryNotFoundException::withAlpha2NotActive($alpha2);
        }

        return $country;
    }

    public function getEnabledCountryByAlpha3(string $alpha3): Country
    {
        $country = $this->countryRepository->findByAlpha3($alpha3);

        if (!$country) {
            throw CountryNotFoundException::withAlpha3($alpha3);
        }

        if (!$country->isEnabled()) {
            throw CountryNotFoundException::withAlpha3NotActive($alpha3);
        }

        return $country;
    }
}
