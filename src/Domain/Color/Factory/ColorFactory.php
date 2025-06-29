<?php

declare(strict_types=1);

namespace App\Domain\Color\Factory;

use App\Domain\Color\Entity\Color;
use App\Domain\Translation\Service\TranslationInitializer;
use App\Infrastructure\Service\LocaleService;

readonly class ColorFactory
{
    public function __construct(private LocaleService $localeService)
    {
    }

    public function create(int $code): Color
    {
        $color = Color::create($code);
        TranslationInitializer::prepare($color, $this->localeService->getSupportedLocales());

        return $color;
    }
}
