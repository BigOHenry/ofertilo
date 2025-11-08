<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Translation\Interface\TranslatableInterface;
use App\Infrastructure\Persistence\Doctrine\Extension\TranslationQueryExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use App\Domain\Translation\Entity\TranslationEntity;

abstract class BaseRepository extends ServiceEntityRepository
{
    /**
     * Automaticky přidá překlady pro TranslatableInterface entity
     */
    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
    {
        $qb = parent::createQueryBuilder($alias, $indexBy);

        if (is_subclass_of($this->getEntityName(), TranslatableInterface::class)) {
            $this->addTranslationsJoin($qb, $alias);
            $qb->distinct();
        }

        return $qb;
    }

    /**
     * Přidá LEFT JOIN pro překlady
     */
    protected function addTranslationsJoin(QueryBuilder $qb, string $alias): void
    {
        // Kontrola, zda už není přidán
        $joins = $qb->getDQLPart('join');
        foreach ($joins as $joinParts) {
            foreach ($joinParts as $join) {
                if ($join->getAlias() === 't') {
                    return;
                }
            }
        }

        $entityClass = $this->getEntityName();

        $qb->leftJoin(
            TranslationEntity::class,
            't',
            'WITH',
            't.object_class = :_translation_class AND t.object_id = ' . $alias . '.id'
        )
           ->setParameter('_translation_class', $entityClass);
    }
}
