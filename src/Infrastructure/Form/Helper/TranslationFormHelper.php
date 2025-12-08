<?php

declare(strict_types=1);

namespace App\Infrastructure\Form\Helper;

use App\Domain\Translation\Entity\TranslationEntity;
use App\Domain\Translation\Interface\TranslatableInterface;
use App\Infrastructure\Service\LocaleService;
use Doctrine\Common\Collections\ArrayCollection;

final readonly class TranslationFormHelper
{
    public function __construct(
        private LocaleService $localeService,
    ) {
    }

    /**
     * @template T of TranslatableInterface
     *
     * @param class-string<T> $entityClass
     *
     * @throws \Exception
     *
     * @return array{translations: ArrayCollection<int, TranslationEntity>}
     */
    public function prepareFormData(string $entityClass): array
    {
        return [
            'translations' => $this->prepareTranslations($entityClass),
        ];
    }

    /**
     * @return ArrayCollection<int, TranslationEntity>
     */
    public function prepareTranslationsFromEntity(TranslatableInterface $entity): ArrayCollection
    {
        $entityClass = $entity::class;
        $translatableFields = $entityClass::getTranslatableFields();
        $supportedLocales = $this->localeService->getSupportedLocales();
        $existingTranslations = $entity->getTranslations();

        $translations = new ArrayCollection();

        foreach ($supportedLocales as $locale) {
            foreach ($translatableFields as $field) {
                $found = false;
                foreach ($existingTranslations as $trans) {
                    if ($trans->getLocale() === $locale && $trans->getField() === $field) {
                        $translations->add($trans);
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $translation = new TranslationEntity();
                    $translation->setLocale($locale);
                    $translation->setField($field);
                    $translation->setValue('');
                    $translations->add($translation);
                }
            }
        }

        return $translations;
    }

    /**
     * @template T of TranslatableInterface
     *
     * @param class-string<T> $entityClass
     *
     * @throws \Exception
     *
     * @return ArrayCollection<int, TranslationEntity>
     */
    private function prepareTranslations(string $entityClass): ArrayCollection
    {
        /* @phpstan-ignore-next-line */
        if (is_subclass_of($entityClass, TranslatableInterface::class) === false) {
            throw new \Exception(\sprintf('Entity class %s must implement %s', $entityClass, TranslatableInterface::class));
        }
        $translatableFields = $entityClass::getTranslatableFields();
        $supportedLocales = $this->localeService->getSupportedLocales();

        $translations = new ArrayCollection();
        foreach ($supportedLocales as $locale) {
            foreach ($translatableFields as $field) {
                $translation = new TranslationEntity();
                $translation->setObjectClass($entityClass);
                $translation->setLocale($locale);
                $translation->setField($field);
                $translation->setValue('');
                $translations->add($translation);
            }
        }

        return $translations;
    }
}
