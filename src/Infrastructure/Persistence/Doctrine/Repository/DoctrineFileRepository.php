<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Shared\File\Entity\File;
use App\Domain\Shared\File\Exception\FileNotFoundException;
use App\Domain\Shared\File\Repository\FileRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<File>
 */
class DoctrineFileRepository extends ServiceEntityRepository implements FileRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    public function save(File $file): void
    {
        $this->getEntityManager()->persist($file);
        $this->getEntityManager()->flush();
    }

    public function delete(File $file): void
    {
        $this->getEntityManager()->remove($file);
        $this->getEntityManager()->flush();
    }

    public function findById(string $id): ?File
    {
        return $this->find($id);
    }

    public function getById(string $id): File
    {
        return $this->findById($id) ?? throw FileNotFoundException::withId($id);
    }

    public function findByFilename(string $filename): ?File
    {
        return $this->findOneBy(['filename' => $filename]);
    }
}
