<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Twig;

use App\Domain\Shared\File\Entity\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExtension extends AbstractExtension
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('file_url', [$this, 'getFileUrl']),
        ];
    }

    public function getFileUrl(?File $file): ?string
    {
        if ($file === null) {
            return null;
        }

        return $this->urlGenerator->generate('secure_file', ['id' => $file->getId()]);
    }
}
