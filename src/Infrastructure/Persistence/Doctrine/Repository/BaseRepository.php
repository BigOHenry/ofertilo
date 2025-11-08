<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Translation\Entity\TranslationEntity;
use App\Domain\Translation\Interface\TranslatableInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @template T of object
 *
 * @extends ServiceEntityRepository<T>
 */
abstract class BaseRepository extends ServiceEntityRepository
{
    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        $qb = parent::createQueryBuilder($alias, $indexBy);

        if (is_subclass_of($this->getEntityName(), TranslatableInterface::class)) {
            $this->addTranslationsJoin($qb, $alias);
            $qb->distinct();
        }

        return $qb;
    }

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
           ->setParameter('_translation_class', $entityClass)
        ;
    }
}
