<?php

declare(strict_types=1);

namespace App\Domain\Material\Factory;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\ValueObject\Type;
use App\Domain\Translation\Service\TranslationInitializer;
use App\Infrastructure\Service\LocaleService;

readonly class MaterialFactory
{
    public function __construct(private LocaleService $localeService)
    {
    }

    public function createNew(): Material
    {
        $material = Material::createEmpty();
        TranslationInitializer::prepare($material, $this->localeService->getSupportedLocales());

        return $material;
    }

    public function create(Type $type, string $name): Material
    {
        $material = Material::create($type, $name);
        TranslationInitializer::prepare($material, $this->localeService->getSupportedLocales());

        return $material;
    }
}
