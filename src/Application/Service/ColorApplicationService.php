<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Color\Entity\Color;
use App\Domain\Color\Exception\ColorNotFoundException;
use App\Domain\Color\Repository\ColorRepositoryInterface;

readonly class ColorApplicationService
{
    public function __construct(
        private ColorRepositoryInterface $colorRepository,
    ) {
    }

    public function findByCode(int $code): ?Color
    {
        return $this->colorRepository->findByCode($code);
    }

    public function findById(int $id): ?Color
    {
        return $this->colorRepository->findById($id);
    }

    /**
     * @throws ColorNotFoundException
     */
    public function getByCode(int $code): Color
    {
        return $this->colorRepository->getByCode($code);
    }

    /**
     * @throws ColorNotFoundException
     */
    public function getById(int $id): Color
    {
        return $this->colorRepository->getById($id);
    }

    public function save(Color $color): void
    {
        $this->colorRepository->save($color);
    }

    public function delete(Color $color): void
    {
        $this->colorRepository->remove($color);
    }
}
