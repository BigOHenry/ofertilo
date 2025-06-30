<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Application\User\Command\CreateUserCommand;
use App\Application\User\Factory\UserCommandFactory;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\InvalidUserException;
use App\Domain\User\Exception\UserAlreadyExistsException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Role;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserApplicationService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private UserCommandFactory $commandFactory,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function createFromCommand(CreateUserCommand $command): User
    {
        $email = $command->getEmail();
        $name = $command->getName();

        if ($email === null) {
            throw InvalidUserException::emptyEmail();
        }
        if ($name === null) {
            throw InvalidUserException::emptyName();
        }

        if ($this->userRepository->findByEmail($email)) {
            throw UserAlreadyExistsException::withEmail();
        }

        $user = User::create($email);

        if ($command->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $command->getPassword());
            $user->setPassword($hashedPassword);
        }

        $user->setName($name);
        $user->setRoles($command->getRoles());
        $user->setForcePasswordChange($command->isForcePasswordChange());
        $user->setForceEmailChange($command->isForceEmailChange());

        $twoFaEnabled = $command->isTwoFaEnabled();
        if ($this->parameterBag->get('app.force_superadmin_2fa') && $this->isSuperAdmin($command->getRoles())) {
            $twoFaEnabled = true;
        }
        $user->setTwoFactorEnabled($twoFaEnabled);

        $this->userRepository->save($user);

        return $user;
    }

    public function isSystemInstalled(): bool
    {
        return $this->userRepository->hasSuperAdmin();
    }

    public function createSuperAdmin(string $email, string $password): User
    {
        $command = $this->commandFactory->createSuperAdminCommand($email, $password);

        return $this->createFromCommand($command);
    }

    public function save(User $user): void
    {
        $this->userRepository->save($user);
    }

    public function delete(User $user): void
    {
        $this->userRepository->remove($user);
    }

    /**
     * @param string[]|Role[] $roles
     */
    private function isSuperAdmin(array $roles): bool
    {
        return \in_array(Role::SUPER_ADMIN, $roles, true)
            || \in_array(Role::SUPER_ADMIN->value, $roles, true);
    }
}
