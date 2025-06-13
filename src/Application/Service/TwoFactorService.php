<?php

namespace App\Application\Service;


use App\Domain\User\User;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;

readonly class TwoFactorService
{
    public function __construct(
        private TotpAuthenticatorInterface $totpAuthenticator
    ) {}

    public function generateSecret(): string
    {
        return $this->totpAuthenticator->generateSecret();
    }

    public function enableTwoFactor(User $user, string $secret): void
    {
        $user->setTotpSecret($secret);
        $user->setTwoFactorEnabled(true);
    }

    public function disableTwoFactor(User $user): void
    {
        $user->setTotpSecret(null);
        $user->setTwoFactorEnabled(false);
    }

    public function needsSetup(User $user): bool
    {
        return !$user->isTotpAuthenticationEnabled();
    }
}
