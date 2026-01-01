<?php

declare(strict_types=1);

namespace App\Application\Color\Command\EditColor;

use App\Application\Color\Service\ColorApplicationService;
use App\Domain\Color\Exception\ColorAlreadyExistsException;
use App\Domain\Color\Exception\ColorValidationException;
use App\Domain\Color\Validator\ColorValidator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditColorCommandHandler
{
    public function __construct(
        private ColorApplicationService $colorApplicationService,
    ) {
    }

    public function __invoke(EditColorCommand $command): void
    {
        $color = $this->colorApplicationService->getById($command->getId());

        $errors = ColorValidator::validate($command->getCode());

        if (!empty($errors)) {
            throw ColorValidationException::withErrors($errors);
        }

        if ($color->getCode() !== $command->getCode()) {
            $foundColor = $this->colorApplicationService->findByCode($command->getCode());
            if ($foundColor !== null && $foundColor->getId() !== $color->getId()) {
                throw ColorAlreadyExistsException::withCode($command->getCode());
            }
        }

        $color->setCode($command->getCode())
              ->setInStock($command->isInStock())
              ->setEnabled($command->isEnabled())
        ;

        foreach ($command->getTranslations() as $translation) {
            $value = mb_trim($translation->getValue() ?? '');
            $color->addOrUpdateTranslation($translation->getField(), $value, $translation->getLocale());
        }

        $this->colorApplicationService->save($color);
    }
}
