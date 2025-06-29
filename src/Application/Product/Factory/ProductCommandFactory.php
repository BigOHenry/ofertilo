<?php

declare(strict_types=1);

namespace App\Application\Product\Factory;

use App\Application\Product\Command\CreateProductColorCommand;
use App\Application\Product\Command\CreateProductCommand;
use App\Application\Product\Command\EditProductColorCommand;
use App\Application\Product\Command\EditProductCommand;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductColor;
use App\Domain\Translation\Entity\TranslationEntity;
use App\Infrastructure\Service\LocaleService;
use Doctrine\Common\Collections\ArrayCollection;

final readonly class ProductCommandFactory
{
    public function __construct(
        private LocaleService $localeService,
    ) {
    }

    public function createCreateCommand(): CreateProductCommand
    {
        $command = new CreateProductCommand();

        $translations = new ArrayCollection();
        foreach ($this->localeService->getSupportedLocales() as $locale) {
            $translation = new TranslationEntity();
            $translation->setLocale($locale);
            $translation->setField('description');
            $translation->setValue('');
            $translations->add($translation);
        }
        $command->setTranslations($translations);

        return $command;
    }

    public function createEditCommand(Product $product): EditProductCommand
    {
        $command = new EditProductCommand();
        $command->setType($product->getType());
        $command->setCountry($product->getCountry());
        $command->setEnabled($product->isEnabled());

        $translations = new ArrayCollection();
        foreach ($this->localeService->getSupportedLocales() as $locale) {
            $translation = new TranslationEntity();
            $translation->setLocale($locale);
            $translation->setField('description');
            $translation->setValue($product->getDescription($locale) ?? '');
            $translations->add($translation);
        }
        $command->setTranslations($translations);

        return $command;
    }

    public function createCreateColorCommand(Product $product): CreateProductColorCommand
    {
        return new CreateProductColorCommand($product);
    }

    public function createEditColorCommand(ProductColor $productColor): EditProductColorCommand
    {
        $command = new EditProductColorCommand($productColor->getProduct());
        $command->setColor($productColor->getColor());
        $command->setDescription($productColor->getDescription());

        return $command;
    }
}
