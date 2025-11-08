<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\EventListener;

use App\Domain\Translation\Entity\TranslationEntity;
use App\Domain\Translation\Interface\TranslatableInterface;
use App\Infrastructure\Service\LocaleService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Proxy;

#[AsDoctrineListener(event: Events::postLoad)]
#[AsDoctrineListener(event: Events::preFlush)]
#[AsDoctrineListener(event: Events::postFlush)]
class TranslationListener
{
    private array $pendingTranslations = [];

    public function __construct(
        private readonly LocaleService $localeService
    ) {}

    private function loadTranslationsForEntity(TranslatableInterface $entity, $em): void
    {
        // Nastavíme default locale z LocaleService
        $entity->setDefaultLocale($this->localeService->getCurrentLocale());

        $entityId = $entity->getId();
        if ($entityId === null) {
            return;
        }

        // Pokud je to proxy, ujistíme se, že je inicializovaný
        if ($entity instanceof Proxy && !$entity->__isInitialized()) {
            $entity->__load();
        }

        // Načteme překlady z databáze
        $translations = $em->getRepository(TranslationEntity::class)->findBy([
            'object_class' => $em->getClassMetadata(get_class($entity))->getName(),
            'object_id' => $entityId,
        ]);

        // Nastavíme překlady do entity
        $translationsCollection = new ArrayCollection($translations);
        $entity->setTranslationsCollection($translationsCollection);
    }

    /**
     * Automaticky načte překlady po načtení entity z DB
     */
    public function postLoad(PostLoadEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof TranslatableInterface) {
            return;
        }

        $this->loadTranslationsForEntity($entity, $args->getObjectManager());
    }

    /**
     * Před flush zpracujeme všechny změny v překladech
     */
    public function preFlush(PreFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        // Zpracujeme všechny načtené entity
        foreach ($uow->getIdentityMap() as $className => $entities) {
            foreach ($entities as $entity) {
                if (!$entity instanceof TranslatableInterface) {
                    continue;
                }

                // Pro lazy-loaded entity, které ještě nemají načtené překlady
                if ($entity->getTranslations()->isEmpty() && $entity->getId() !== null) {
                    $this->loadTranslationsForEntity($entity, $em);
                }

                $this->processEntityTranslations($entity, $em);
            }
        }

        // Zpracujeme nové entity (scheduled for insert)
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof TranslatableInterface) {
                $this->pendingTranslations[spl_object_id($entity)] = $entity;
                $entity->setDefaultLocale($this->localeService->getCurrentLocale());
            }
        }

        // Zpracujeme odstraněné entity
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (!$entity instanceof TranslatableInterface) {
                continue;
            }

            // Odstraníme všechny překlady
            $this->removeAllTranslations($entity, $em);
        }
    }

    /**
     * Po flush nastavíme object_id pro nové entity
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        if (empty($this->pendingTranslations)) {
            return;
        }

        $em = $args->getObjectManager();
        $needsFlush = false;

        foreach ($this->pendingTranslations as $objectId => $entity) {
            if ($entity->getId() === null) {
                continue;
            }

            foreach ($entity->getTranslations() as $translation) {
                if ($translation->getObjectId() === null || $translation->getObjectId() === 0) {
                    $translation->setObjectId($entity->getId());
                    $em->persist($translation);
                    $needsFlush = true;
                }
            }
        }

        $this->pendingTranslations = [];

        if ($needsFlush) {
            $em->flush();
        }
    }

    /**
     * Zpracuje změny v překladech konkrétní entity
     */
    private function processEntityTranslations(TranslatableInterface $entity, $em): void
    {
        $entityId = $entity->getId();
        if ($entityId === null) {
            return; // Nová entita, zpracuje se v postFlush
        }

        $translationRepo = $em->getRepository(TranslationEntity::class);

        // Načteme aktuální překlady z DB
        $existingTranslations = $translationRepo->findBy([
            'object_class' => get_class($entity),
            'object_id' => $entityId,
        ]);

        // Vytvoříme mapu existujících překladů
        $existingMap = [];
        foreach ($existingTranslations as $translation) {
            $key = $translation->getField() . '_' . $translation->getLocale();
            $existingMap[$key] = $translation;
        }

        // Zpracujeme překlady z entity
        $processedKeys = [];
        foreach ($entity->getTranslations() as $translation) {
            $key = $translation->getField() . '_' . $translation->getLocale();
            $processedKeys[] = $key;

            if ($translation->getObjectId() === null) {
                $translation->setObjectId($entityId);
            }
            if (empty($translation->getObjectClass())) {
                $translation->setObjectClass(get_class($entity));
            }

            $em->persist($translation);
        }

        // Odstraníme překlady, které už nejsou v kolekci
        foreach ($existingMap as $key => $existingTranslation) {
            if (!in_array($key, $processedKeys, true)) {
                $em->remove($existingTranslation);
            }
        }
    }

    /**
     * Odstraní všechny překlady entity
     */
    private function removeAllTranslations(TranslatableInterface $entity, $em): void
    {
        $entityId = $entity->getId();
        if ($entityId === null) {
            return;
        }

        $translationRepo = $em->getRepository(TranslationEntity::class);
        $translations = $translationRepo->findBy([
            'object_class' => get_class($entity),
            'object_id' => $entityId,
        ]);

        foreach ($translations as $translation) {
            $em->remove($translation);
        }
    }
}