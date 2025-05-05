<?php

namespace App\Domain\Translation;

interface TranslationRepositoryInterface
{
    public function find(object $entity, string $field, string $locale): ?string;

    public function save(object $entity, string $field, string $locale, string $value): void;
}
