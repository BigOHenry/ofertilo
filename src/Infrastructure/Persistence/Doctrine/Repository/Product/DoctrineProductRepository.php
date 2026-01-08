<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository\Product;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Exception\ProductNotFoundException;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Shared\Country\Entity\Country;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseRepository;
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

    public function findByTypeAndCountry(ProductType $type, ?Country $country): ?Product
    {
        return $this->createQueryBuilder('p')
                    ->where('p INSTANCE OF :entityClass')
                    ->andWhere('p.country = :country')
                    ->setParameter('entityClass', Product::getProductClassByType($type))
                    ->setParameter('country', $country)
                    ->getQuery()
                    ->getOneOrNullResult()
        ;
    }

    public function findById(string $id): ?Product
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @throws ProductNotFoundException
     */
    public function getById(string $id): Product
    {
        return $this->findById($id) ?? throw ProductNotFoundException::withId($id);
    }
}
