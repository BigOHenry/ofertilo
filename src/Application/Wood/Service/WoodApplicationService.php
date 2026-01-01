<?php

declare(strict_types=1);

namespace App\Application\Wood\Service;

use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Exception\WoodNotFoundException;
use App\Domain\Wood\Repository\WoodRepositoryInterface;

readonly class WoodApplicationService
{
    public function __construct(
        private WoodRepositoryInterface $woodRepository,
    ) {
    }

    public function findByName(string $name): ?Wood
    {
        return $this->woodRepository->findByName($name);
    }

    public function findById(int $id): ?Wood
    {
        return $this->woodRepository->findById($id);
    }

    /**
     * @throws WoodNotFoundException
     */
    public function getByName(string $name): Wood
    {
        return $this->woodRepository->getByName($name);
    }

    /**
     * @throws WoodNotFoundException
     */
    public function getById(int $id): Wood
    {
        return $this->woodRepository->getById($id);
    }

    public function save(Wood $wood): void
    {
        $this->woodRepository->save($wood);
    }

    public function delete(Wood $wood): void
    {
        $this->woodRepository->remove($wood);
    }
}
