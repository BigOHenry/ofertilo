<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class LocaleRedirectListener
{
    private array $supportedLocales = ['cs', 'en'];
    private string $defaultLocale = 'cs';

    public function __construct(private RouterInterface $router)
    {}

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        // přeskakuj systémové routy (začínají _ nebo jsou z profilu/debugu)
        if (str_starts_with($route, '_')) {
            return;
        }

        $locale = $request->attributes->get('_locale');
        if (!$locale || !in_array($locale, ['cs', 'en'])) {
            $preferred = $request->getPreferredLanguage(['cs', 'en']) ?? 'cs';
            $uri = $request->getRequestUri();
            $newUri = '/' . $preferred . $uri;

            $event->setController(fn() => new RedirectResponse($newUri));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'onKernelController'];
    }
}
