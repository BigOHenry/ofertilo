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
    private ?string $defaultLocale = null;
    private bool $translationsLoaded = false;

    public function setDefaultLocale(string $locale): void
    {
        $this->defaultLocale = $locale;
    }

    public function getDefaultLocale(): ?string
    {
        return $this->defaultLocale;
    }

    /**
     * @return Collection<int, TranslationEntity>
     */
    public function getTranslations(): Collection
    {
        $this->initializeTranslations();

        return $this->translations;
    }

    /**
     * @param Collection<int, TranslationEntity> $translations
     */
    public function setTranslationsCollection(Collection $translations): void
    {
        $this->translations = $translations;
        $this->translationsLoaded = true;
    }

    public function addOrUpdateTranslation(string $field, ?string $value, string $locale): void
    {
        $this->initializeTranslations();

        foreach ($this->translations as $t) {
            if ($t->getField() === $field && $t->getLocale() === $locale) {
                $t->setValue($value);

                return;
            }
        }

        $this->addTranslation($field, $value, $locale);
    }

    public function getTranslationValue(string $field, ?string $locale = null): ?string
    {
        $this->initializeTranslations();

        $targetLocale = $locale ?? $this->defaultLocale ?? 'en';

        if (!$this->translationsLoaded && $this->getId() !== null) {
            $this->lazyLoadTranslations();
        }

        foreach ($this->translations as $translation) {
            if ($translation->getField() === $field && $translation->getLocale() === $targetLocale) {
                return $translation->getValue();
            }
        }

        return null;
    }

    public function addTranslation(string $field, ?string $value, string $locale): void
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
        $this->translationsLoaded = true;
    }

    public function removeTranslation(string $field, string $locale): void
    {
        $this->initializeTranslations();
        foreach ($this->translations as $key => $translation) {
            if ($translation->getField() === $field && $translation->getLocale() === $locale) {
                $this->translations->remove($key);

                return;
            }
        }
    }

    public function clearTranslations(): void
    {
        $this->initializeTranslations();
        $this->translations->clear();
    }

    abstract public function getId(): ?int;

    private function initializeTranslations(): void
    {
        if (!isset($this->translations)) {
            $this->translations = new ArrayCollection();
        }
    }

    private function lazyLoadTranslations(): void
    {
        dump('lazyLoadTranslations');
        // Pokud už jsou načtené, nedelej nic
        if ($this->translationsLoaded) {
            return;
        }

        // Získáme EntityManager pomocí globální služby (fallback)
        // V produkci by měl být preferován event listener
        global $entityManager;

        if (isset($entityManager)) {
            $translations = $entityManager
                ->getRepository(TranslationEntity::class)
                ->findBy([
                    'object_class' => static::class,
                    'object_id' => $this->getId(),
                ])
            ;

            foreach ($translations as $translation) {
                $this->translations->add($translation);
            }
        }

        $this->translationsLoaded = true;
    }
}
