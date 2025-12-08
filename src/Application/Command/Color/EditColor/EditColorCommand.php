<?php

declare(strict_types=1);

namespace App\Application\Command\Color\EditColor;

use App\Domain\Translation\Entity\TranslationEntity;
use App\Domain\Translation\TranslationDto\TranslationDto;
use Symfony\Component\Form\FormInterface;

final readonly class EditColorCommand
{
    /**
     * @param array<int, TranslationDto> $translations
     */
    public function __construct(
        private int $id,
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

        return new self((int) $data['id'], $data['code'], $data['inStock'], $data['enabled'] ?? true, $translations);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function isInStock(): bool
    {
        return $this->inStock;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return array<int, TranslationEntity>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
