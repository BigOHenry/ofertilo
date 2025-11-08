<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Product\Entity\ProductColor;
use App\Domain\Product\Repository\ProductColorRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends ServiceEntityRepository<\App\Domain\Product\Entity\ProductColor>
 */
class DoctrineProductColorRepository extends ServiceEntityRepository implements ProductColorRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductColor::class);
    }

    public function save(ProductColor $productColor): void
    {
        $this->getEntityManager()->persist($productColor);
        $this->getEntityManager()->flush();
    }

    public function remove(ProductColor $productColor): void
    {
        $this->getEntityManager()->remove($productColor);
        $this->getEntityManager()->flush();
    }
}
