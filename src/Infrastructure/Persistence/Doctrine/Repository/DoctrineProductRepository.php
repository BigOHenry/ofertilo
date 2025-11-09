<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends BaseRepository<Product>
 */
class DoctrineProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $product): void
    {
        $em = $this->getEntityManager();
        $em->persist($product);
        $em->flush();
    }

    public function remove(Product $product): void
    {
        $this->getEntityManager()->remove($product);
        $this->getEntityManager()->flush();
    }

    public function findByTypeAndCountry(Type $type, Country $country): ?Product
    {
        return $this->findOneBy(['type' => $type, 'country' => $country]);
    }

    public function findById(int $id): ?Product
    {
        return $this->findOneBy(['id' => $id]);
    }
}
