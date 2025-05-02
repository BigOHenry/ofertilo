<?php

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Material\Material;
use App\Domain\Material\MaterialRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineMaterialRepository extends ServiceEntityRepository implements MaterialRepositoryInterface
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Material::class);
    }

    /**
     * @param Material $material
     * @return void
     */
    public function save(Material $material): void
    {
        $this->getEntityManager()->persist($material);
        $this->getEntityManager()->flush();
    }

    public function findByName(string $name): ?Material
    {
        return $this->findOneBy(['name' => $name]);
    }
}