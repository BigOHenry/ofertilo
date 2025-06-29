<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Infrastructure\Service\LocaleService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class LocaleRedirectListener
{
    public function __construct(private LocaleService $localeService)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // skip internal Symfony routes
        $excluded = ['/_wdt', '/_profiler', '/_fragment', '/_error', '/favicon.ico'];
        if (array_any($excluded, static fn ($prefix) => str_starts_with($path, $prefix))) {
            return;
        }

        if (str_starts_with($path, '/2fa')) {
            return;
        }

        // If the URL already contains a valid locale, do nothing
        if (preg_match('#^/(' . implode('|', $this->localeService->getSupportedLocales()) . ')(/|$)#', $path)) {
            return;
        }

        $locale = $request->getPreferredLanguage($this->localeService->getSupportedLocales()) ?? $this->localeService->getDefaultLocale();

        // Redirect to locale-prefixed URL
        $event->setResponse(new RedirectResponse('/' . $locale . $path));
    }
}
