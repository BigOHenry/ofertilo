<?php

declare(strict_types=1);

namespace App\Application\Command\Wood\EditWood;

use App\Application\Service\WoodApplicationService;
use App\Domain\Wood\Exception\WoodAlreadyExistsException;
use App\Domain\Wood\Exception\WoodNotFoundException;
use App\Domain\Wood\Exception\WoodValidationException;
use App\Domain\Wood\Validator\WoodValidator;
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

        $errors = WoodValidator::validate(
            $command->getName(),
            $command->getLatinName(),
            $command->getDryDensity(),
            $command->getHardness()
        );

        if (!empty($errors)) {
            throw WoodValidationException::withErrors($errors);
        }

        $wood->setName($command->getName())
              ->setLatinName($command->getLatinName())
              ->setDryDensity($command->getDryDensity())
              ->setHardness($command->getHardness())
              ->setEnabled($command->isEnabled())
        ;

        foreach ($command->getTranslations() as $translation) {
            $value = mb_trim($translation->getValue() ?? '');
            $wood->addOrUpdateTranslation($translation->getField(), $value, $translation->getLocale());
        }

        $this->woodApplicationService->save($wood);
    }
}
