<?php

declare(strict_types=1);

namespace App\Domain\Translation\Repository;

use App\Domain\Translation\Interface\TranslatableInterface;

interface TranslationLoaderInterface
{
    public function loadTranslations(TranslatableInterface $entity): void;
}
