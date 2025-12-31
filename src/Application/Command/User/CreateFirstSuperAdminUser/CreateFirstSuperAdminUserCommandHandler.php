<?php

declare(strict_types=1);

namespace App\Application\Command\User\CreateFirstSuperAdminUser;

use App\Application\Service\UserApplicationService;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\Role;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final readonly class CreateFirstSuperAdminUserCommandHandler
{
    public function __construct(
        private UserApplicationService $userApplicationService,
        private UserPasswordHasherInterface $passwordHasher,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function __invoke(CreateFirstSuperAdminUserCommand $command): void
    {
        $user = User::create($command->getEmail(), 'tmp', 'SuperAdmin', Role::SUPER_ADMIN);

        if ($command->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $command->getPassword());
            $user->setPassword($hashedPassword);
        }

        $user->setForcePasswordChange(false);

        $twoFaEnabled = false;
        if ($this->parameterBag->get('app.force_superadmin_2fa') && $this->userApplicationService->isSuperAdmin($user->getRoles())) {
            $twoFaEnabled = true;
        }
        $user->setTwoFactorEnabled($twoFaEnabled);

        $this->userApplicationService->save($user);
    }
}
