<?php

declare(strict_types=1);

namespace App\Domain\Color\Repository;

use App\Domain\Color\Entity\Color;
use Doctrine\ORM\QueryBuilder;

interface ColorRepositoryInterface
{
    public function createQueryBuilder(string $alias): QueryBuilder;

    public function findByCode(int $code): ?Color;

    public function save(Color $color): void;

    public function remove(Color $color): void;

    /**
     * @return Color[]
     */
    public function findOutOfStock(): array;

    public function countOutOfStock(): int;
}
