<?php

declare(strict_types=1);

namespace App\Domain\Translation;

use Doctrine\Common\Collections\Collection;

interface TranslatableInterface
{
    public function getId(): ?int;

    public function addOrUpdateTranslation(string $field, string $value, string $locale): void;

    /**
     * @return string[]
     */
    public static function getTranslatableFields(): array;

    /**
     * @return Collection<int, TranslationEntity>
     */
    public function getTranslations(): Collection;

    public function getTranslationFromMemory(string $field, string $locale): ?string;
}
