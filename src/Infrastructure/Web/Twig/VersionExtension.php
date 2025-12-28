<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Twig;

use App\Infrastructure\Service\VersionService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class VersionExtension extends AbstractExtension
{
    public function __construct(
        private readonly VersionService $versionService,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_version', [$this, 'getVersion']),
        ];
    }

    public function getVersion(): string
    {
        return $this->versionService->getVersion();
    }
}
