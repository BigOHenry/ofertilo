<?php

declare(strict_types=1);

namespace App\Domain\Translation\Interface;

use App\Domain\Translation\Entity\TranslationEntity;
use Doctrine\Common\Collections\Collection;

interface TranslatableInterface
{
    public function getId(): string;

    public function addOrUpdateTranslation(string $field, string $value, string $locale): void;

    public function getTranslationValue(string $field, string $locale): ?string;

    /**
     * @return string[]
     */
    public static function getTranslatableFields(): array;

    /**
     * @return Collection<int, TranslationEntity>
     */
    public function getTranslations(): Collection;

    public function removeTranslation(string $field, string $locale): void;

    public function setDefaultLocale(string $locale): void;

    public function getDefaultLocale(): ?string;

    /**
     * @param Collection<int, TranslationEntity> $translations
     */
    public function setTranslationsCollection(Collection $translations): void;
}
