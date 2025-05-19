<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Translation\TranslationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineTranslationRepository implements TranslationRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function find(object $entity, string $field, string $locale): ?string
    {
        $class = new \ReflectionClass($entity)->getShortName();
        $id = method_exists($entity, 'getId') ? $entity->getId() : null;
        if (!$id) {
            return null;
        }

        $repo = $this->em->getRepository(TranslationEntity::class);

        return $repo->findOneBy([
            'object_class' => $class,
            'object_id' => $id,
            'field' => $field,
            'locale' => $locale,
        ])?->getValue();
    }

    public function save(object $entity, string $field, string $locale, string $value): void
    {
        $class = new \ReflectionClass($entity)->getShortName();
        $id = method_exists($entity, 'getId') ? $entity->getId() : null;
        if (!$id) {
            return;
        }

        $repo = $this->em->getRepository(TranslationEntity::class);
        $translation = $repo->findOneBy([
            'object_class' => $class,
            'object_id' => $id,
            'field' => $field,
            'locale' => $locale,
        ]) ?? new TranslationEntity();

        $translation->setObjectClass($class);
        $translation->setObjectId($id);
        $translation->setField($field);
        $translation->setLocale($locale);
        $translation->setValue($value);

        $this->em->persist($translation);
        $this->em->flush();
    }
}
