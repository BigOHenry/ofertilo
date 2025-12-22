<?php

declare(strict_types=1);

namespace App\Application\Command\Wood\CreateWood;

use App\Application\Service\WoodApplicationService;
use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Exception\WoodAlreadyExistsException;
use App\Domain\Wood\Exception\WoodValidationException;
use App\Domain\Wood\Validator\WoodValidator;
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

        $errors = WoodValidator::validate(
            $command->getName(),
            $command->getLatinName(),
            $command->getDryDensity(),
            $command->getHardness()
        );

        if (!empty($errors)) {
            throw WoodValidationException::withErrors($errors);
        }

        $wood = Wood::create($name, $command->getLatinName(), $command->getDryDensity(), $command->getHardness());

        foreach ($command->getTranslations() as $translation) {
            $value = mb_trim($translation->getValue() ?? '');
            $wood->addOrUpdateTranslation($translation->getField(), $value, $translation->getLocale());
        }

        $this->woodApplicationService->save($wood);
    }
}
