<?php

declare(strict_types=1);

namespace App\Domain\Translation\Repository;

use App\Domain\Product\Entity\Product;
use App\Domain\Translation\Interface\TranslatableInterface;
use Doctrine\ORM\QueryBuilder;

interface TranslationLoaderInterface
{
    public function loadTranslations(TranslatableInterface $entity): void;
}
