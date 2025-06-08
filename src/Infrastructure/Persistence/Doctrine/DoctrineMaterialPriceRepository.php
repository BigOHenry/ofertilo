<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Material\MaterialPrice;
use App\Domain\Material\MaterialPriceRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends ServiceEntityRepository<MaterialPrice>
 */
class DoctrineMaterialPriceRepository extends ServiceEntityRepository implements MaterialPriceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaterialPrice::class);
    }

    public function save(MaterialPrice $price): void
    {
        $this->getEntityManager()->persist($price);
        $this->getEntityManager()->flush();
    }

    public function remove(MaterialPrice $price): void
    {
        $this->getEntityManager()->remove($price);
        $this->getEntityManager()->flush();
    }
}
