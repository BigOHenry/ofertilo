<?php declare(strict_types=1);

namespace App\Domain\Shared\File\Repository;

use App\Domain\Shared\File\Entity\File;

interface FileRepositoryInterface
{
    public function save(File $file): void;

    public function delete(File $file): void;

    public function findById(string $id): ?File;

    public function getById(string $id): File;

    public function findByFilename(string $filename): ?File;
}