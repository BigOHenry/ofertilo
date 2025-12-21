<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Infrastructure\Service\LocaleService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

readonly class RedirectToLocaleController
{
    public function __construct(private LocaleService $localeService)
    {
    }

    #[Route(path: '/', name: 'homepage_redirect')]
    public function redirect(Request $request): RedirectResponse
    {
        $locale = $request->getPreferredLanguage($this->localeService->getSupportedLocales()) ?? $this->localeService->getDefaultLocale();

        return new RedirectResponse('/' . $locale . '/');
    }
}
