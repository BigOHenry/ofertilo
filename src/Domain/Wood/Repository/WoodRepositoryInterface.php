<?php

declare(strict_types=1);

namespace App\Domain\Wood\Repository;

use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Exception\WoodNotFoundException;
use Doctrine\ORM\QueryBuilder;

interface WoodRepositoryInterface
{
    public function createQueryBuilder(string $alias): QueryBuilder;

    public function findByName(string $name): ?Wood;

    public function findById(string $id): ?Wood;

    /**
     * @throws WoodNotFoundException
     */
    public function getByName(string $name): Wood;

    /**
     * @throws WoodNotFoundException
     */
    public function getById(string $id): Wood;

    public function save(Wood $wood): void;

    public function remove(Wood $wood): void;
}
