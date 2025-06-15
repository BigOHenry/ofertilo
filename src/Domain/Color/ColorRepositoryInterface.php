<?php

declare(strict_types=1);

namespace App\Domain\Color;

use Doctrine\ORM\QueryBuilder;

interface ColorRepositoryInterface
{
    public function createQueryBuilder(string $alias): QueryBuilder;

    public function findByName(string $name): ?Color;

    public function save(Color $color): void;

    public function remove(Color $color): void;
}
