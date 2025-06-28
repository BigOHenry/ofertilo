<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends ServiceEntityRepository<\App\Domain\Product\Entity\Product>
 */
class DoctrineProductRepository extends ServiceEntityRepository implements ProductRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly DoctrineTranslationLoader $translationLoader)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $product): void
    {
        $em = $this->getEntityManager();
        $em->persist($product);
        $em->flush();

        foreach ($product->getTranslations() as $translation) {
            if ($translation->getId() === null) {
                $product_id = $product->getId();

                if ($product_id !== null) {
                    $translation->setObjectId($product_id);
                    $em->persist($translation);
                }
            }
        }

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

    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?Product
    {
        $product = parent::find($id, $lockMode, $lockVersion);
        if ($product) {
            $this->translationLoader->loadTranslations($product);
        }

        return $product;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?Product
    {
        $product = parent::findOneBy($criteria, $orderBy);
        if ($product) {
            $this->translationLoader->loadTranslations($product);
        }

        return $product;
    }
}
