<?php

declare(strict_types=1);

namespace App\Application\Command\Color\CreateColor;

use App\Domain\Translation\Entity\TranslationEntity;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormInterface;

final readonly class CreateColorCommand
{
    /**
     * @param int        $code
     * @param bool       $inStock
     * @param bool       $enabled
     * @param Collection<int, TranslationEntity> $translations
     */
    public function __construct(
        private int $code,
        private bool $inStock,
        private bool $enabled,
        private Collection $translations,
    ) {
    }

    public static function createFromForm(FormInterface $form): self
    {
        $data = $form->getData();

        return new self($data['code'], $data['inStock'], $data['enabled'] ?? true, $data['translations']);
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
     * @return Collection<int, TranslationEntity>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }
}
