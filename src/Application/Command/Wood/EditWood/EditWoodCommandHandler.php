<?php

declare(strict_types=1);

namespace App\Application\Command\Wood\EditWood;

use App\Application\Service\WoodApplicationService;
use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Exception\WoodAlreadyExistsException;
use App\Domain\Wood\Exception\WoodNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditWoodCommandHandler
{
    public function __construct(
        private WoodApplicationService $woodApplicationService,
    ) {
    }

    public function __invoke(EditWoodCommand $command): void
    {
        $wood = $this->woodApplicationService->findById($command->getId());

        if ($wood === null) {
            throw WoodNotFoundException::withId($command->getId());
        }

        if ($wood->getName() !== $command->getName()) {
            $foundWood = $this->woodApplicationService->findById($command->getId());
            if ($foundWood !== null && $foundWood->getId() !== $command->getId()) {
                throw WoodAlreadyExistsException::withName($command->getName());
            }
        }

        $wood->setName($command->getName())
              ->setLatinName($command->getLatinName())
              ->setDryDensity($command->getDryDensity())
              ->setHardness($command->getHardness())
              ->setEnabled($command->isEnabled());

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
