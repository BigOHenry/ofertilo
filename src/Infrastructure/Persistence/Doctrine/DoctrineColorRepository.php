<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Color\Entity\Color;
use App\Domain\Color\Repository\ColorRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends ServiceEntityRepository<\App\Domain\Color\Entity\Color>
 */
class DoctrineColorRepository extends ServiceEntityRepository implements ColorRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly DoctrineTranslationLoader $translationLoader)
    {
        parent::__construct($registry, Color::class);
    }

    public function save(Color $color): void
    {
        $em = $this->getEntityManager();
        $em->persist($color);
        $em->flush();

        foreach ($color->getTranslations() as $translation) {
            if ($translation->getId() === null) {
                $color_id = $color->getId();

                if ($color_id !== null) {
                    $translation->setObjectId($color_id);
                    $em->persist($translation);
                }
            }
        }

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

    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?Color
    {
        $color = parent::find($id, $lockMode, $lockVersion);
        if ($color) {
            $this->translationLoader->loadTranslations($color);
        }

        return $color;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?Color
    {
        $color = parent::findOneBy($criteria, $orderBy);
        if ($color) {
            $this->translationLoader->loadTranslations($color);
        }

        return $color;
    }

    /**
     * @return Color[]
     */
    public function findOutOfStock(): array
    {
        return $this->createQueryBuilder('c')
                    ->where('c.enabled = :enabled AND c.in_stock = :in_stock')
                    ->setParameter('enabled', true)
                    ->setParameter('in_stock', false)
                    ->orderBy('c.code', 'ASC')
                    ->getQuery()
                    ->getResult()
        ;
    }

    public function countOutOfStock(): int
    {
        return (int) $this->createQueryBuilder('c')
                          ->select('COUNT(c.id)')
                          ->where('c.enabled = :enabled AND c.in_stock = :in_stock')
                          ->setParameter('enabled', true)
                          ->setParameter('in_stock', false)
                          ->getQuery()
                          ->getSingleScalarResult()
        ;
    }

    public function findAvailableColors(array $excludeIds = []): array
    {
        $qb = $this->createQueryBuilder('c')
                   ->where('c.enabled = :enabled')
                   ->setParameter('enabled', true)
                   ->orderBy('c.code', 'ASC');

        if (!empty($excludeIds)) {
            $qb->andWhere('c.id NOT IN (:excludeIds)')
               ->setParameter('excludeIds', $excludeIds);
        }

        return $qb->getQuery()->getResult();
    }
}
