<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Color\Entity\Color;
use App\Domain\Color\Repository\ColorRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends ServiceEntityRepository<\App\Domain\Color\Entity\Color>
 */
class DoctrineColorRepository extends ServiceEntityRepository implements ColorRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, private readonly DoctrineTranslationLoader $translationLoader)
    {
        parent::__construct($registry, Color::class);
    }

    public function save(Color $color): void
    {
        $em = $this->getEntityManager();
        $em->persist($color);
        $em->flush();

        foreach ($color->getTranslations() as $translation) {
            if ($translation->getId() === null) {
                $translation->setObjectId($color->getId());
                $em->persist($translation);
            }
        }

        $em->flush();
    }

    public function remove(Color $color): void
    {
        $this->getEntityManager()->remove($color);
        $this->getEntityManager()->flush();
    }

    public function findByName(string $name): ?Color
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?Color
    {
        $color = parent::find($id, $lockMode, $lockVersion);
        if ($color) {
            $this->translationLoader->loadTranslations($color);
        }

        return $color;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?Color
    {
        $color = parent::findOneBy($criteria, $orderBy);
        if ($color) {
            $this->translationLoader->loadTranslations($color);
        }

        return $color;
    }
}
