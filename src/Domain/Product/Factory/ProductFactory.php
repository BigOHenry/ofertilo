<?php

declare(strict_types=1);

namespace App\Domain\Product\Factory;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use App\Domain\Translation\Service\TranslationInitializer;
use App\Infrastructure\Service\LocaleService;

readonly class ProductFactory
{
    public function __construct(private LocaleService $localeService)
    {
    }

    public function createEmpty(): Product
    {
        $product = Product::createEmpty();
        TranslationInitializer::prepare($product, $this->localeService->getSupportedLocales());

        return $product;
    }

    public function create(Type $type, Country $country): Product
    {
        $product = Product::create($type, $country);
        TranslationInitializer::prepare($product, $this->localeService->getSupportedLocales());

        return $product;
    }
}
