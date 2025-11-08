<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Repository\MaterialRepositoryInterface;
use App\Domain\Material\ValueObject\Type;
use App\Domain\Wood\Entity\Wood;
use Doctrine\Persistence\ManagerRegistry;

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

    public function findByWoodAndType(Wood $wood, Type $type): ?Material
    {
        return $this->findOneBy(['wood' => $wood, 'type' => $type]);
    }

    public function findById(int $id): ?Material
    {
        return $this->findOneBy(['id' => $id]);
    }
}
