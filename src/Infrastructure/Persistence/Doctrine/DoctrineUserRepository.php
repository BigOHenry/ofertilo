<?php

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\User\Role;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineUserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param string $email
     * @return User|null
     */
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
        $sql = 'SELECT 1 FROM "user" WHERE roles @> :role LIMIT 1';

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('role', json_encode([Role::SUPER_ADMIN], JSON_THROW_ON_ERROR));
        $result = $stmt->executeQuery();

        return $result->fetchOne() !== false;
    }

    /**
     * @param User $user
     * @return void
     */
    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}