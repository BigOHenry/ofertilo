<?php

declare(strict_types=1);

namespace App\Application\Command\Color\CreateColor;

use App\Domain\Translation\Entity\TranslationEntity;
use App\Domain\Translation\TranslationDto\TranslationDto;
use Symfony\Component\Form\FormInterface;

final readonly class CreateColorCommand
{
    /**
     * @param array<int, TranslationDto> $translations
     */
    public function __construct(
        private int $code,
        private bool $inStock,
        private bool $enabled,
        private array $translations,
    ) {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        $translations = [];
        /** @var TranslationEntity $translation */
        foreach ($data['translations'] as $translation) {
            $translations[] = TranslationDto::createTranslationDtoFromEntity($translation);
        }

        return new self($data['code'], $data['inStock'], $data['enabled'] ?? true, $translations);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isInStock(): bool
    {
        return $this->inStock;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return array<int, TranslationDto>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
