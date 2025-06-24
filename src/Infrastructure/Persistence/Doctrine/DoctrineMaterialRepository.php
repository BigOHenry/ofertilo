<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Repository\MaterialRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends ServiceEntityRepository<\App\Domain\Material\Entity\Material>
 */
class DoctrineMaterialRepository extends ServiceEntityRepository implements MaterialRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly DoctrineTranslationLoader $translationLoader)
    {
        parent::__construct($registry, Material::class);
    }

    public function save(Material $material): void
    {
        $em = $this->getEntityManager();
        $em->persist($material);
        $em->flush();

        foreach ($material->getTranslations() as $translation) {
            if ($translation->getId() === null) {
                $material_id = $material->getId();

                if ($material_id !== null) {
                    $translation->setObjectId($material_id);
                    $em->persist($translation);
                }
            }
        }

        $em->flush();
    }

    public function remove(Material $material): void
    {
        $this->getEntityManager()->remove($material);
        $this->getEntityManager()->flush();
    }

    public function findByName(string $name): ?Material
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?Material
    {
        $material = parent::find($id, $lockMode, $lockVersion);
        if ($material) {
            $this->translationLoader->loadTranslations($material);
        }

        return $material;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?Material
    {
        $material = parent::findOneBy($criteria, $orderBy);
        if ($material) {
            $this->translationLoader->loadTranslations($material);
        }

        return $material;
    }
}
