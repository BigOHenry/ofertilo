<?php declare(strict_types=1);

namespace App\Application\Query;

use Symfony\Component\HttpFoundation\Request;

abstract readonly class PaginatedGridQuery
{
    protected function __construct(private ?int $page, private ?int $size, private ?string $sortField, private ?string $sortDirection) {
    }

    public static function createFormRequest(Request $request): static
    {
        $sortData = $request->query->all('sort');
        $sortField = $sortData['field'] ?? null;
        $sortDir = $sortData['dir'] ?? 'asc';

        return new static($request->query->get('page') ?? 1, $request->query->get('size') ?? 10, $sortField, $sortDir);
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