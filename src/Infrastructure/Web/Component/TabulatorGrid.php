<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('tabulator_grid')]
final class TabulatorGrid
{
    public string $id;
    public string $apiUrl;
    public array $columns; /** @phpstan-ignore-line */
    public int $paginationSize = 10;
    public bool $pagination = true;
    public bool $sortMode = true;
    public ?string $height = 'auto';
    public string $layout = 'fitColumns';
    public array $ajaxParams = []; /** @phpstan-ignore-line */
    public ?string $windowVariable = null;
    public bool $showActions = false;
    public ?string $editRoute = null;
    public ?string $deleteRoute = null;
    public ?string $rowClickRoute = null;
    public ?string $editModalId = 'editModal';

    public function getHeight(): string
    {
        return $this->height ?? ($this->pagination ? '500px' : 'auto');
    }

    public function getWindowVariable(): string
    {
        return $this->windowVariable ?? str_replace('-', '_', $this->id);
    }
}
