<?php

declare(strict_types=1);

namespace App\Application\Command\Color\EditColor;

use App\Application\Service\ColorApplicationService;
use App\Domain\Color\Exception\ColorAlreadyExistsException;
use App\Domain\Color\Exception\ColorNotFoundException;
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
        $color = $this->colorApplicationService->findById($command->getId());

        if ($color === null) {
            throw ColorNotFoundException::withId($command->getId());
        }

        if ($color->getCode() !== $command->getCode()) {
            $foundColor = $this->colorApplicationService->findById($command->getId());
            if ($foundColor !== null && $foundColor->getId() !== $command->getId()) {
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
