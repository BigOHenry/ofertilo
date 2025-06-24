<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Symfony\Component\HttpFoundation\RequestStack;

readonly class LocaleService
{
    /**
     * @param string[] $supportedLocales
     */
    public function __construct(
        private array $supportedLocales,
        private string $defaultLocale,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @return string[]
     */
    public function getSupportedLocales(): array
    {
        return $this->supportedLocales;
    }

    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    public function getCurrentLocale(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request?->getLocale() ?? $this->defaultLocale;
    }

    public function isLocaleSupported(string $locale): bool
    {
        return \in_array($locale, $this->supportedLocales, true);
    }

    public function getFallbackLocale(string $locale): string
    {
        // If the locale is supported, return it
        if ($this->isLocaleSupported($locale)) {
            return $locale;
        }

        // If the locale is in cs_CZ format, try cs
        if (str_contains($locale, '_')) {
            $baseLocale = mb_substr($locale, 0, 2);
            if ($this->isLocaleSupported($baseLocale)) {
                return $baseLocale;
            }
        }

        return $this->defaultLocale;
    }
}
