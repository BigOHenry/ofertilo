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

            return $this->getGitVersion();
        });
    }

    private function getGitVersion(): string
    {
        $gitDir = $this->projectDir . '/.git';

        if (!is_dir($gitDir)) {
            return 'unknown';
        }

        try {
            $tagRaw = shell_exec('git describe --tags --abbrev=0 2>/dev/null');

            if ($tagRaw === null || $tagRaw === false) {
                $tagRaw = shell_exec('git rev-parse --short HEAD 2>/dev/null');
            }

            if ($tagRaw === null || $tagRaw === false) {
                return 'dev';
            }

            $tag = mb_trim($tagRaw);

            if (!empty($tag)) {
                return $tag;
            }

            return 'dev';
        } catch (\Throwable) {
            return 'dev';
        }
    }
}
