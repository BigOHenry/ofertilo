<?php

declare(strict_types=1);

namespace App\Application\Command\Color\CreateColor;

use App\Application\Service\ColorApplicationService;
use App\Domain\Color\Entity\Color;
use App\Domain\Color\Exception\ColorAlreadyExistsException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateColorCommandHandler
{
    public function __construct(
        private ColorApplicationService $colorApplicationService,
    ) {
    }

    public function __invoke(CreateColorCommand $command): void
    {
        $code = $command->getCode();

        if ($this->colorApplicationService->findByCode($code)) {
            throw ColorAlreadyExistsException::withCode($code);
        }

        $color = Color::create($code, $command->isInStock(), $command->isEnabled());

        foreach ($command->getTranslations() as $translation) {
            $value = mb_trim($translation->getValue() ?? '');
            $color->addOrUpdateTranslation($translation->getField(), $value,$translation->getLocale());
        }

        $this->colorApplicationService->save($color);
    }
}
