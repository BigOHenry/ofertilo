<?php

declare(strict_types=1);

namespace App\Application\Color\Command\CreateColor;

use App\Application\Color\Service\ColorApplicationService;
use App\Domain\Color\Entity\Color;
use App\Domain\Color\Exception\ColorAlreadyExistsException;
use App\Domain\Color\Exception\ColorValidationException;
use App\Domain\Color\Validator\ColorValidator;
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

        if ($this->colorApplicationService->findByCode($code) !== null) {
            throw ColorAlreadyExistsException::withCode($code);
        }

        $errors = ColorValidator::validate($command->getCode());

        if (!empty($errors)) {
            throw ColorValidationException::withErrors($errors);
        }

        $color = Color::create($code, $command->isInStock(), $command->isEnabled());

        foreach ($command->getTranslations() as $translation) {
            $value = mb_trim($translation->getValue() ?? '');
            $color->addOrUpdateTranslation($translation->getField(), $value, $translation->getLocale());
        }

        $this->colorApplicationService->save($color);
    }
}
