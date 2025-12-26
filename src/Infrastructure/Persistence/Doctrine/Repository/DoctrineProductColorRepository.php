<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductColor;
use App\Domain\Product\Repository\ProductColorRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends BaseRepository<ProductColor>
 */
class DoctrineProductColorRepository extends BaseRepository implements ProductColorRepositoryInterface
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

    public function findByProductAndColor(Product $product, Color $color): ?ProductColor
    {
        return $this->findOneBy(['product' => $product, 'color' => $color]);
    }

    public function findById(int $id): ?ProductColor
    {
        return $this->findOneBy(['id' => $id]);
    }
}
