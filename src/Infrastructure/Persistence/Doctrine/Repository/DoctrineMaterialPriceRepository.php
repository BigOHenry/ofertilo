<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Repository\MaterialPriceRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends BaseRepository<MaterialPrice>
 */
class DoctrineMaterialPriceRepository extends BaseRepository implements MaterialPriceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaterialPrice::class);
    }

    public function save(MaterialPrice $materialPrice): void
    {
        $em = $this->getEntityManager();
        $em->persist($materialPrice);
        $em->flush();
    }

    public function remove(MaterialPrice $materialPrice): void
    {
        $this->getEntityManager()->remove($materialPrice);
        $this->getEntityManager()->flush();
    }

    public function findByMaterialAndThickness(Material $material, int $thickness): ?MaterialPrice
    {
        return $this->findOneBy(['material' => $material, 'thickness' => $thickness]);
    }

    public function findById(int $id): ?MaterialPrice
    {
        return $this->findOneBy(['id' => $id]);
    }
}
