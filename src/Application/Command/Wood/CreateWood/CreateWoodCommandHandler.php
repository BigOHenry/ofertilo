<?php

declare(strict_types=1);

namespace App\Application\Command\Wood\CreateWood;

use App\Application\Service\WoodApplicationService;
use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Exception\WoodAlreadyExistsException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateWoodCommandHandler
{
    public function __construct(
        private WoodApplicationService $woodApplicationService,
    ) {
    }

    public function __invoke(CreateWoodCommand $command): void
    {
        $name = $command->getName();

        if ($this->woodApplicationService->findByName($name)) {
            throw WoodAlreadyExistsException::withName($name);
        }

        $wood = Wood::create($name, $command->getLatinName(), $command->getDryDensity(), $command->getHardness());

        foreach ($command->getTranslations() as $translation) {
            $value = mb_trim($translation->getValue() ?? '');
            if ($translation->getField() === Wood::TRANSLATION_FIELD_DESCRIPTION) {
                if (!empty($value)) {
                    $wood->setDescription($value, $translation->getLocale());
                } else {
                    $wood->setDescription(null, $translation->getLocale());
                }
            } elseif ($translation->getField() === Wood::TRANSLATION_FIELD_PLACE_OF_ORIGIN) {
                if (!empty($value)) {
                    $wood->setPlaceOfOrigin($value, $translation->getLocale());
                } else {
                    $wood->setPlaceOfOrigin(null, $translation->getLocale());
                }
            }
        }

        $this->woodApplicationService->save($wood);
    }
}
