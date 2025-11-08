<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\EventListener;

use App\Domain\Translation\Entity\TranslationEntity;
use App\Domain\Translation\Interface\TranslatableInterface;
use App\Infrastructure\Service\LocaleService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Proxy;

#[AsDoctrineListener(event: Events::postLoad)]
#[AsDoctrineListener(event: Events::preFlush)]
#[AsDoctrineListener(event: Events::postFlush)]
class TranslationListener
{
    /**
     * @var array<int, TranslatableInterface>
     */
    private array $pendingTranslations = [];

    public function __construct(
        private readonly LocaleService $localeService,
    ) {
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof TranslatableInterface) {
            return;
        }

        $this->loadTranslationsForEntity($entity, $args->getObjectManager());
    }

    public function preFlush(PreFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getIdentityMap() as $className => $entities) {
            foreach ($entities as $entity) {
                if (!$entity instanceof TranslatableInterface) {
                    continue;
                }

                if ($entity->getId() !== null && $entity->getTranslations()->isEmpty()) {
                    $this->loadTranslationsForEntity($entity, $em);
                }

                $this->processEntityTranslations($entity, $em);
            }
        }

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof TranslatableInterface) {
                $this->pendingTranslations[spl_object_id($entity)] = $entity;
                $entity->setDefaultLocale($this->localeService->getCurrentLocale());
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (!$entity instanceof TranslatableInterface) {
                continue;
            }

            $this->removeAllTranslations($entity, $em);
        }
    }

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

    private function loadTranslationsForEntity(TranslatableInterface $entity, EntityManagerInterface $em): void
    {
        $entity->setDefaultLocale($this->localeService->getCurrentLocale());

        $entityId = $entity->getId();
        if ($entityId === null) {
            return;
        }

        if ($entity instanceof Proxy && !$entity->__isInitialized()) {
            $entity->__load();
        }

        $translations = $em->getRepository(TranslationEntity::class)->findBy([
            'object_class' => $em->getClassMetadata($entity::class)->getName(),
            'object_id' => $entityId,
        ]);

        $translationsCollection = new ArrayCollection($translations);
        $entity->setTranslationsCollection($translationsCollection);
    }

    private function processEntityTranslations(TranslatableInterface $entity, EntityManagerInterface $em): void
    {
        $entityId = $entity->getId();
        if ($entityId === null) {
            return;
        }

        $translationRepo = $em->getRepository(TranslationEntity::class);

        $existingTranslations = $translationRepo->findBy([
            'object_class' => $entity::class,
            'object_id' => $entityId,
        ]);

        $existingMap = [];
        foreach ($existingTranslations as $translation) {
            $key = $translation->getField() . '_' . $translation->getLocale();
            $existingMap[$key] = $translation;
        }

        $processedKeys = [];
        foreach ($entity->getTranslations() as $translation) {
            $key = $translation->getField() . '_' . $translation->getLocale();
            $processedKeys[] = $key;

            if ($translation->getObjectId() === null) {
                $translation->setObjectId($entityId);
            }
            if (empty($translation->getObjectClass())) {
                $translation->setObjectClass($entity::class);
            }

            $em->persist($translation);
        }

        foreach ($existingMap as $key => $existingTranslation) {
            if (!\in_array($key, $processedKeys, true)) {
                $em->remove($existingTranslation);
            }
        }
    }

    private function removeAllTranslations(TranslatableInterface $entity, EntityManagerInterface $em): void
    {
        $entityId = $entity->getId();
        if ($entityId === null) {
            return;
        }

        $translationRepo = $em->getRepository(TranslationEntity::class);
        $translations = $translationRepo->findBy([
            'object_class' => $entity::class,
            'object_id' => $entityId,
        ]);

        foreach ($translations as $translation) {
            $em->remove($translation);
        }
    }
}
