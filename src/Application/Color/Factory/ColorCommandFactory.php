<?php

namespace App\Application\Color\Factory;

use App\Application\Color\Command\CreateColorCommand;
use App\Application\Color\Command\EditColorCommand;
use App\Domain\Color\Entity\Color;
use App\Domain\Translation\Entity\TranslationEntity;
use App\Infrastructure\Service\LocaleService;
use Doctrine\Common\Collections\ArrayCollection;

final readonly class ColorCommandFactory
{
    public function __construct(
        private LocaleService $localeService
    ) {}

    public function createCreateCommand(): CreateColorCommand
    {
        $command = new CreateColorCommand();

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

    public function createEditCommand(Color $color): EditColorCommand
    {
        $command = new EditColorCommand();
        $command->setCode($color->getCode());
        $command->setInStock($color->isInStock());
        $command->setEnabled($color->isEnabled());
        $command->setTranslations($color->getTranslations());
        return $command;
    }
}
