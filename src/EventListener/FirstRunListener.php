<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Domain\User\UserRepositoryInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;

readonly class FirstRunListener
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RouterInterface $router,
        private CacheInterface $cache,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        // Ignore redirects, AJAX and non-GET
        if (!$request->isMethod('GET') || $request->isXmlHttpRequest()) {
            return;
        }

        // Do not redirect if we are already on the "create-super-admin" page
        $route = $request->attributes->get('_route');

        if (\in_array($route, [
            'app_first_run_setup',
            '_wdt', '_profiler', '_profiler_home', '_profiler_exception', '_profiler_router',
        ], true)) {
            return;
        }

        $path = $request->getPathInfo();

        if (str_starts_with($path, '/_') // profiler a debug
            || str_starts_with($path, '/build') // encore assets
            || str_starts_with($path, '/favicon')
            || str_starts_with($path, '/apple-touch-icon')
            || preg_match('#\.(css|js|png|jpg|svg|woff2?)$#', $path)) {
            return;
        }

        $userRepository = $this->userRepository;
        // Check if there is a superAdministrator
        $is_installed = $this->cache->get('ofertilo.first_run_done', function () use ($userRepository) {
            return $userRepository->hasSuperAdmin();
        });

        if (!$is_installed) {
            $event->setController(function () {
                return new RedirectResponse(
                    $this->router->generate('app_first_run_setup')
                );
            });
        }
    }
}
