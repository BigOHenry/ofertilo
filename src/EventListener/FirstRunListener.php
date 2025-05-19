<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Domain\User\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

readonly class FirstRunListener
{
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private RouterInterface $router,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Ignore redirects, AJAX and non-GET
        if (!$request->isMethod('GET') || $request->isXmlHttpRequest()) {
            return;
        }

        // Do not redirect if we are already on the "create-super-admin" page
        if ($request->getPathInfo() === '/first-run') {
            return;
        }

        // Check if there is a superAdministrator
        if (!$this->userRepo->hasSuperAdmin()) {
            $event->setResponse(new RedirectResponse(
                $this->router->generate('app_first_run_setup')
            ));
        }
    }
}
