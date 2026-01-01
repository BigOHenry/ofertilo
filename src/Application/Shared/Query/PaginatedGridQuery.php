<?php

declare(strict_types=1);

namespace App\Application\Shared\Query;

use Symfony\Component\HttpFoundation\Request;

/** @phpstan-consistent-constructor */
readonly class PaginatedGridQuery
{
    protected function __construct(private int $page, private int $size, private ?string $sortField, private ?string $sortDirection)
    {
    }

    public static function createFormRequest(Request $request): static
    {
        $data = $request->query->all();
        $sortField = $data['sortField'] ?? null;
        $sortDir = $data['sortDir'] ?? 'asc';

        return new static((int) ($request->query->get('page') ?? 1), (int) ($request->query->get('size') ?? 10), $sortField, $sortDir);
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getSortField(): ?string
    {
        return $this->sortField;
    }

    public function getSortDirection(): ?string
    {
        return $this->sortDirection;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->size;
    }
}
