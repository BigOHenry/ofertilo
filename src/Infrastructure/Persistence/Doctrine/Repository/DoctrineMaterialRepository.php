<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Exception\MaterialNotFoundException;
use App\Domain\Material\Repository\MaterialRepositoryInterface;
use App\Domain\Material\ValueObject\MaterialType;
use App\Domain\Wood\Entity\Wood;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends BaseRepository<Material>
 */
class DoctrineMaterialRepository extends BaseRepository implements MaterialRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Material::class);
    }

    public function save(Material $material): void
    {
        $em = $this->getEntityManager();
        $em->persist($material);
        $em->flush();
    }

    public function remove(Material $material): void
    {
        $this->getEntityManager()->remove($material);
        $this->getEntityManager()->flush();
    }

    public function findByWoodAndType(Wood $wood, MaterialType $type): ?Material
    {
        return $this->createQueryBuilder('m')
                    ->where('m INSTANCE OF :entityClass')
                    ->andWhere('m.wood = :wood')
                    ->setParameter('entityClass', Material::getMaterialClassByType($type))
                    ->setParameter('wood', $wood)
                    ->getQuery()
                    ->getOneOrNullResult()
        ;
    }

    public function findById(int $id): ?Material
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @throws MaterialNotFoundException
     */
    public function getById(int $id): Material
    {
        return $this->findById($id) ?? throw MaterialNotFoundException::withId($id);
    }
}
