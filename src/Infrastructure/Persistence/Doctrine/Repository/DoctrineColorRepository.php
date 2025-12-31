<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Color\Entity\Color;
use App\Domain\Color\Exception\ColorNotFoundException;
use App\Domain\Color\Repository\ColorRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends BaseRepository<Color>
 */
class DoctrineColorRepository extends BaseRepository implements ColorRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Color::class);
    }

    public function save(Color $color): void
    {
        $em = $this->getEntityManager();
        $em->persist($color);
        $em->flush();
    }

    public function remove(Color $color): void
    {
        $this->getEntityManager()->remove($color);
        $this->getEntityManager()->flush();
    }

    public function findByCode(int $code): ?Color
    {
        return $this->findOneBy(['code' => $code]);
    }

    public function findById(int $id): ?Color
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @throws ColorNotFoundException
     */
    public function getByCode(int $code): Color
    {
        return $this->findByCode($code) ?? throw ColorNotFoundException::withCode($code);
    }

    /**
     * @throws ColorNotFoundException
     */
    public function getById(int $id): Color
    {
        return $this->findById($id) ?? throw ColorNotFoundException::withId($id);
    }

    /**
     * @return Color[]
     */
    public function findOutOfStock(): array
    {
        return $this->createQueryBuilder('c')
                    ->where('c.enabled = :enabled AND c.inStock = :inStock')
                    ->setParameter('enabled', true)
                    ->setParameter('inStock', false)
                    ->orderBy('c.code', 'ASC')
                    ->getQuery()
                    ->getResult()
        ;
    }

    public function countOutOfStock(): int
    {
        return (int) $this->createQueryBuilder('c')
                          ->select('COUNT(c.id)')
                          ->where('c.enabled = :enabled AND c.inStock = :inStock')
                          ->setParameter('enabled', true)
                          ->setParameter('inStock', false)
                          ->getQuery()
                          ->getSingleScalarResult()
        ;
    }

    /**
     * @param int[] $excludeIds
     *
     * @return Color[]
     */
    public function findAvailableColors(array $excludeIds = []): array
    {
        $qb = $this->createQueryBuilder('c')
                   ->where('c.enabled = :enabled')
                   ->setParameter('enabled', true)
                   ->orderBy('c.code', 'ASC')
        ;

        if (!empty($excludeIds)) {
            $qb->andWhere('c.id NOT IN (:excludeIds)')
               ->setParameter('excludeIds', $excludeIds)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
