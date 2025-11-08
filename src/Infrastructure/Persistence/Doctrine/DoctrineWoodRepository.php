<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Repository\WoodRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends ServiceEntityRepository<\App\Domain\Wood\Entity\Wood>
 */
class DoctrineWoodRepository extends ServiceEntityRepository implements WoodRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly DoctrineTranslationLoader $translationLoader)
    {
        parent::__construct($registry, Wood::class);
    }

    public function save(Wood $wood): void
    {
        $em = $this->getEntityManager();
        $em->persist($wood);
        $em->flush();

        foreach ($wood->getTranslations() as $translation) {
            if ($translation->getId() === null) {
                $material_id = $wood->getId();

                if ($material_id !== null) {
                    $translation->setObjectId($material_id);
                    $em->persist($translation);
                }
            }
        }

        $em->flush();
    }

    public function remove(Wood $wood): void
    {
        $this->getEntityManager()->remove($wood);
        $this->getEntityManager()->flush();
    }

    public function findByName(string $name): ?Wood
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findById(int $id): ?Wood
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?Wood
    {
        $wood = parent::find($id, $lockMode, $lockVersion);
        if ($wood) {
            $this->translationLoader->loadTranslations($wood);
        }

        return $wood;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?Wood
    {
        $wood = parent::findOneBy($criteria, $orderBy);
        if ($wood) {
            $this->translationLoader->loadTranslations($wood);
        }

        return $wood;
    }
}
