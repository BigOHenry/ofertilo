<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository\Product;

use App\Domain\Product\Entity\ProductVariant;
use App\Domain\Product\Exception\ProductVariantNotFoundException;
use App\Domain\Product\Repository\ProductVariantRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductVariant>
 */
class DoctrineProductVariantRepository extends ServiceEntityRepository implements ProductVariantRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductVariant::class);
    }

    public function findById(string $id): ?ProductVariant
    {
        return $this->find($id);
    }

    public function getById(string $id): ProductVariant
    {
        return $this->findById($id) ?? throw ProductVariantNotFoundException::withId($id);
    }
}
