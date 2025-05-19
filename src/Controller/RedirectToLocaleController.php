<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

readonly class RedirectToLocaleController
{
    public function __construct(private array $supportedLocales, private string $defaultLocale)
    {
    }

    #[Route(path: '/', name: 'homepage_redirect')]
    public function redirect(Request $request): RedirectResponse
    {
        $locale = $request->getPreferredLanguage($this->supportedLocales) ?? $this->defaultLocale;

        return new RedirectResponse('/' . $locale . '/');
    }
}
