<?php

declare(strict_types=1);

namespace App\Application\Command\Color\EditColor;

use App\Domain\Translation\Entity\TranslationEntity;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormInterface;

final readonly class EditColorCommand
{
    /**
     * @param Collection<int, TranslationEntity> $translations
     */
    public function __construct(
        private int $id,
        private int $code,
        private bool $inStock,
        private bool $enabled,
        private Collection $translations,
    ) {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self((int) $data['id'], $data['code'], $data['inStock'], $data['enabled'] ?? true, $data['translations']);
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
     * @return Collection<int, TranslationEntity>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }
}
