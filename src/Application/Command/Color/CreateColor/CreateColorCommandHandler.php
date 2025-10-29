<?php

declare(strict_types=1);

namespace App\Application\Command\Color\CreateColor;

use App\Application\Color\ColorApplicationService;
use App\Domain\Color\Entity\Color;
use App\Domain\Color\Exception\ColorAlreadyExistsException;
use App\Domain\Color\Exception\InvalidColorException;
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
            if (!empty($value)) {
                $color->setDescription($value, $translation->getLocale());
            } else {
                $color->setDescription(null, $translation->getLocale());
            }
        }

        $this->colorApplicationService->save($color);
    }
}
