<?php

declare(strict_types=1);

namespace App\Application\Material\Factory;

use App\Application\Material\Command\CreateMaterialCommand;
use App\Application\Material\Command\CreateMaterialPriceCommand;
use App\Application\Material\Command\EditMaterialCommand;
use App\Application\Material\Command\EditMaterialPriceCommand;
use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Translation\Entity\TranslationEntity;
use App\Infrastructure\Service\LocaleService;
use Doctrine\Common\Collections\ArrayCollection;

final readonly class MaterialCommandFactory
{
    public function __construct(
        private LocaleService $localeService,
    ) {
    }

    public function createCreateCommand(): CreateMaterialCommand
    {
        $command = new CreateMaterialCommand();

        $translations = new ArrayCollection();
        foreach ($this->localeService->getSupportedLocales() as $locale) {
            // Description translation
            $descriptionTranslation = new TranslationEntity();
            $descriptionTranslation->setLocale($locale);
            $descriptionTranslation->setField('description');
            $descriptionTranslation->setValue('');
            $translations->add($descriptionTranslation);

            // Place of origin translation
            $placeTranslation = new TranslationEntity();
            $placeTranslation->setLocale($locale);
            $placeTranslation->setField('place_of_origin');
            $placeTranslation->setValue('');
            $translations->add($placeTranslation);
        }
        $command->setTranslations($translations);

        return $command;
    }

    public function createEditCommand(Material $material): EditMaterialCommand
    {
        $command = new EditMaterialCommand();
        $command->setName($material->getName());
        $command->setType($material->getType());
        $command->setLatinName($material->getLatinName());
        $command->setDryDensity($material->getDryDensity());
        $command->setHardness($material->getHardness());
        $command->setEnabled($material->isEnabled());
        $command->setTranslations($material->getTranslations());

        return $command;
    }

    public function createCreatePriceCommand(Material $material): CreateMaterialPriceCommand
    {
        return new CreateMaterialPriceCommand($material);
    }

    public function createEditPriceCommand(MaterialPrice $materialPrice): EditMaterialPriceCommand
    {
        $command = new EditMaterialPriceCommand($materialPrice->getMaterial());
        $command->setThickness($materialPrice->getThickness());
        $command->setPrice($materialPrice->getPrice());

        return $command;
    }
}
