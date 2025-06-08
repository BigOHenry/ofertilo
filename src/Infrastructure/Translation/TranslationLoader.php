<?php

declare(strict_types=1);

namespace App\Infrastructure\Translation;

use App\Domain\Translation\TranslatableInterface;
use App\Domain\Translation\TranslationEntity;
use Doctrine\ORM\EntityManagerInterface;

final class TranslationLoader
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function loadTranslations(TranslatableInterface $entity): void
    {
        $translations = $this->em->getRepository(TranslationEntity::class)->findBy([
            'object_class' => $entity::class,
            'object_id' => $entity->getId(),
        ]);

        usort(
            $translations,
            static fn ($a, $b) => [$a->getField(), $a->getLocale()] <=> [$b->getField(), $b->getLocale()]
        );

        foreach ($translations as $t) {
            $entity->getTranslations()->add($t);
        }
    }
}
