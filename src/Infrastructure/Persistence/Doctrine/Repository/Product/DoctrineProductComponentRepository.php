<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository\Product;

use App\Domain\Product\Entity\ProductComponent;
use App\Domain\Product\Exception\ProductComponentNotFoundException;
use App\Domain\Product\Repository\ProductComponentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductComponent>
 */
class DoctrineProductComponentRepository extends ServiceEntityRepository implements ProductComponentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductComponent::class);
    }

    public function findById(string $id): ?ProductComponent
    {
        return $this->find($id);
    }

    public function getById(string $id): ProductComponent
    {
        return $this->findById($id) ?? throw ProductComponentNotFoundException::withId($id);
    }
}
