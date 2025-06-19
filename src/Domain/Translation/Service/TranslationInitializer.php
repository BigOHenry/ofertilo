<?php

declare(strict_types=1);

namespace App\Domain\Translation\Service;

use App\Domain\Translation\Interface\TranslatableInterface;

class TranslationInitializer
{
    /**
     * @param string[] $locales
     */
    public static function prepare(TranslatableInterface $entity, array $locales): void
    {
        foreach ($entity::getTranslatableFields() as $field) {
            foreach ($locales as $locale) {
                $existing = $entity->getTranslationFromMemory($field, $locale);
                if ($existing === null) {
                    $entity->addOrUpdateTranslation($field, '', $locale);
                }
            }
        }
    }
}
