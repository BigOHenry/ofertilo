<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Repository\WoodRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends BaseRepository<Wood>
 */
class DoctrineWoodRepository extends BaseRepository implements WoodRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wood::class);
    }

    public function save(Wood $wood): void
    {
        $em = $this->getEntityManager();
        $em->persist($wood);
        $em->flush();
    }

    public function remove(Wood $wood): void
    {
        $this->getEntityManager()->remove($wood);
        $this->getEntityManager()->flush();
    }

    public function findByName(string $name): ?Wood
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findById(int $id): ?Wood
    {
        return $this->findOneBy(['id' => $id]);
    }
}
