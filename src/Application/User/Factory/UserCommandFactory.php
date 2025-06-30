<?php

declare(strict_types=1);

namespace App\Application\User\Factory;

use App\Application\User\Command\CreateUserCommand;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\Role;

class UserCommandFactory
{
    public function createCreateCommand(): CreateUserCommand
    {
        return new CreateUserCommand();
    }

    public function createSuperAdminCommand(string $email, string $password): CreateUserCommand
    {
        $command = new CreateUserCommand();
        $command->setEmail($email);
        $command->setPassword($password);
        $command->setName('SuperAdmin');
        $command->setRoles([Role::SUPER_ADMIN]);
        $command->setForceEmailChange(false);
        $command->setForcePasswordChange(false);

        return $command;
    }

    public function createEditCommand(User $user): CreateUserCommand
    {
        $command = new CreateUserCommand();
        $command->setEmail($user->getEmail());
        $command->setName($user->getName());
        $command->setRoles($user->getRoles());
        $command->setForcePasswordChange($user->isForcePasswordChange());
        $command->setForceEmailChange($user->isForceEmailChange());
        $command->setTwoFaEnabled($user->isTwoFactorEnabled());

        return $command;
    }
}
