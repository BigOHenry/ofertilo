<?php

namespace App\Domain\Product\Factory;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use App\Domain\Shared\Repository\CountryRepositoryInterface;
use App\Domain\Translation\Service\TranslationInitializer;

readonly class ProductFactory
{
    public function __construct(
        private CountryRepositoryInterface $countryRepository,
        private array $supportedLocales
    ) {}

    /**
     * Creates a new product with default values for the form
     * @return Product
     */
    public function createNew(): Product
    {
        // Getting default values
        $defaultType = Type::cases()[0]; // First available type
        $defaultCountry = $this->countryRepository->findByAlpha2('CZ');

        if (!$defaultCountry) {
            throw new \RuntimeException('Default country (CZ) not found');
        }

        // Using static factory method from entity
        $product = Product::create($defaultType, $defaultCountry);

        // Initializing translations for the form
        TranslationInitializer::prepare($product, $this->supportedLocales);

        return $product;
    }

    /**
     * Creates a product with specific values
     * @param Type    $type
     * @param Country $country
     * @return Product
     */
    public function create(Type $type, Country $country): Product
    {
        $product = Product::create($type, $country);
        TranslationInitializer::prepare($product, $this->supportedLocales);

        return $product;
    }

    /**
     * Creates a product from form data (for future use)
     * @param array $formData
     * @return Product
     */
    public function createFromFormData(array $formData): Product
    {
        if (!isset($formData['type'], $formData['country'])) {
            throw new \InvalidArgumentException('Type and Country are required');
        }

        $type = $formData['type'] instanceof Type ? $formData['type'] : Type::from($formData['type']);

        $country = $formData['country'] instanceof Country
            ? $formData['country']
            : $this->countryRepository->findById($formData['country']);

        if (!$country) {
            throw new \InvalidArgumentException('Country not found');
        }

        return $this->create($type, $country);
    }
}
