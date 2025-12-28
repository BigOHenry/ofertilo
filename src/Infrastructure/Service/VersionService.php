<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class VersionService
{
    public function __construct(
        private CacheInterface $cache,
        private string $projectDir,
    ) {
    }

    public function getVersion(): string
    {
        return $this->cache->get('app.version', function (ItemInterface $item): string {
            $item->expiresAfter(null);

            return $this->loadVersion();
        });
    }

    private function loadVersion(): string
    {
        $versionFile = $this->projectDir . '/var/version.txt';

        if (file_exists($versionFile)) {
            $content = file_get_contents($versionFile);

            if ($content === false) {
                return 'dev';
            }
            $version = mb_trim($content);

            if (!empty($version)) {
                return $version;
            }
        }

        return 'dev';
    }
}
