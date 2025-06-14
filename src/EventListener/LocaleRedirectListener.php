<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class LocaleRedirectListener
{
    /**
     * @param string[] $supportedLocales
     */
    public function __construct(private array $supportedLocales, private string $defaultLocale)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // přeskočit interní Symfony routy
        $excluded = ['/_wdt', '/_profiler', '/_fragment', '/_error', '/favicon.ico'];
        if (array_any($excluded, static fn ($prefix) => str_starts_with($path, $prefix))) {
            return;
        }

        if (str_starts_with($path, '/2fa')) {
            return;
        }

        // Pokud URL již obsahuje validní locale, nic nedělej
        if (preg_match('#^/(' . implode('|', $this->supportedLocales) . ')(/|$)#', $path)) {
            return;
        }

        // Urči preferovaný jazyk
        $locale = $request->getPreferredLanguage($this->supportedLocales) ?? $this->defaultLocale;

        // Přesměruj na locale-prefixed URL
        $event->setResponse(new RedirectResponse('/' . $locale . $path));
    }
}
