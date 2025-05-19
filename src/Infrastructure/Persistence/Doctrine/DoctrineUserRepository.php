<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\User\Role;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
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

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @throws Exception
     * @throws \JsonException
     */
    public function hasSuperAdmin(): bool
    {
        $sql = 'SELECT 1 FROM appuser WHERE roles @> :role LIMIT 1';

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('role', json_encode([Role::SUPER_ADMIN], \JSON_THROW_ON_ERROR));
        $result = $stmt->executeQuery();

        return $result->fetchOne() !== false;
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
