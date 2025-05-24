<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Domain\User\UserRepositoryInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;

readonly class FirstRunListener
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RouterInterface $router,
        private CacheInterface $cache
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
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

        $userRepository = $this->userRepository;
        // Check if there is a superAdministrator
        $is_installed = $this->cache->get('ofertilo.first_run_done', function () use ($userRepository) {
            return $userRepository->hasSuperAdmin();
        });

        if (!$is_installed) {
            $event->setResponse(new RedirectResponse(
                $this->router->generate('app_first_run_setup')
            ));
        }
    }
}
