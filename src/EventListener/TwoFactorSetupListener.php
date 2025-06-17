<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Domain\User\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class TwoFactorSetupListener implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private RouterInterface $router,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token || !$token->getUser() instanceof User) {
            return;
        }

        /** @var User $user */
        $user = $token->getUser();
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (
            $user->isTwoFactorEnabled()
            && $user->getTotpAuthenticationSecret() === null
            && !\in_array($route, ['app_2fa_setup', 'app_2fa_qr_code', 'app_logout', '2fa_login', '2fa_login_check'], true)
        ) {
            $response = new RedirectResponse($this->router->generate('app_2fa_setup'));
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
