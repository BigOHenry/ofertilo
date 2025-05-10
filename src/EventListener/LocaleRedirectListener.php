<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

readonly class LocaleRedirectListener
{
    public function __construct(private array $supportedLocales, private string $defaultLocale) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

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
