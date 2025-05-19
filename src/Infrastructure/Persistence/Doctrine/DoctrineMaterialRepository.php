<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Material\Material;
use App\Domain\Material\MaterialRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Material>
 */
class DoctrineMaterialRepository extends ServiceEntityRepository implements MaterialRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Material::class);
    }

    public function save(Material $material): void
    {
        $this->getEntityManager()->persist($material);
        $this->getEntityManager()->flush();
    }

    public function delete(Material $material): void
    {
        $this->getEntityManager()->remove($material);
        $this->getEntityManager()->flush();
    }

    public function findByName(string $name): ?Material
    {
        return $this->findOneBy(['name' => $name]);
    }
}
