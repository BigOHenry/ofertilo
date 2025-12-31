<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @phpstan-extends ServiceEntityRepository<User>
 */
class DoctrineUserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findById(int $id): ?User
    {
        return $this->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function getById(int $id): User
    {
        return $this->findById($id) ?? throw UserNotFoundException::withId($id);
    }

    public function getByEmail(string $email): User
    {
        return $this->findByEmail($email) ?? throw UserNotFoundException::withEmail($email);
    }

    public function hasSuperAdmin(): bool
    {
        $sql = 'SELECT 1 FROM appuser WHERE roles @> :role LIMIT 1';

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('role', json_encode([Role::SUPER_ADMIN], \JSON_THROW_ON_ERROR));

        return $stmt->executeQuery()->fetchOne() > 0;
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function remove(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }
}
