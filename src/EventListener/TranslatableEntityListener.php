<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Domain\Translation\Interface\TranslatableInterface;
use App\Domain\Translation\Service\TranslationInitializer;
use App\Infrastructure\Service\LocaleService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postLoad, method: 'postLoad')]
readonly class TranslatableEntityListener
{
    public function __construct(private LocaleService $localeService)
    {
    }

    public function postLoad(object $entity): void
    {
        if ($entity instanceof TranslatableInterface && $entity->getTranslations()->count() === 0) {
            TranslationInitializer::prepare($entity, $this->localeService->getSupportedLocales());
        }
    }
}
