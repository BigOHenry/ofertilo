<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Repository\WoodRepositoryInterface;

final readonly class WoodApplicationService
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

    public function save(Wood $wood): void
    {
        $this->woodRepository->save($wood);
    }

    public function delete(Wood $wood): void
    {
        $this->woodRepository->remove($wood);
    }
}
