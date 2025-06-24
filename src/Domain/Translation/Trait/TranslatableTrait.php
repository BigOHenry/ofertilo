<?php

declare(strict_types=1);

namespace App\Domain\Translation\Trait;

use App\Domain\Translation\Entity\TranslationEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait TranslatableTrait
{
    /**
     * @var Collection<int, TranslationEntity>
     */
    private Collection $translations;

    public function initTranslations(): void
    {
        if (!isset($this->translations)) {
            $this->translations = new ArrayCollection();
        }
    }

    /**
     * @return Collection<int, TranslationEntity>
     */
    public function getTranslations(): Collection
    {
        $this->initTranslations();

        return $this->translations;
    }

    public function addOrUpdateTranslation(string $field, string $value, string $locale): void
    {
        $this->initTranslations();

        foreach ($this->translations as $t) {
            if ($t->getField() === $field && $t->getLocale() === $locale) {
                $t->setValue($value);

                return;
            }
        }

        $this->addTranslation($field, $value, $locale);
    }

    public function addTranslation(string $field, string $value, string $locale): void
    {
        $t = new TranslationEntity();
        $t->setField($field);
        $t->setLocale($locale);
        $t->setValue($value);
        $t->setObjectClass(self::class);

        if ($this->getId() !== null) {
            $t->setObjectId($this->getId());
        }

        $this->translations->add($t);
    }

    public function getTranslationFromMemory(string $field, string $locale): ?string
    {
        $this->initTranslations();

        foreach ($this->translations as $t) {
            if ($t->getField() === $field && $t->getLocale() === $locale) {
                return $t->getValue();
            }
        }

        return null;
    }

    /**
     * @return TranslationEntity[]
     */
    public function exportTranslations(): array
    {
        $result = [];

        foreach ($this->translations as $t) {
            if ($t->getObjectId() === null && $this->getId() !== null) {
                $t->setObjectId($this->getId());
            }

            if (!$t->getObjectClass()) {
                $t->setObjectClass(self::class);
            }

            $result[] = $t;
        }

        return $result;
    }

    /**
     * @param TranslationEntity[]|Collection<int, TranslationEntity> $translations
     */
    public function setTranslations(array|Collection $translations): void
    {
        $this->initTranslations();
        $this->translations->clear();

        foreach ($translations as $t) {
            $this->translations->add($t);
        }
    }
}
