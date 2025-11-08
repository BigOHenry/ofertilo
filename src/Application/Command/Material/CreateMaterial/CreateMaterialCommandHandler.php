<?php

declare(strict_types=1);

namespace App\Application\Command\Material\CreateMaterial;

use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Entity\Material;
use App\Domain\Material\Exception\MaterialAlreadyExistsException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateMaterialCommandHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    public function __invoke(CreateMaterialCommand $command): void
    {
        $wood = $command->getWood();
        $type = $command->getType();

        if ($this->materialApplicationService->findByWoodAndType($wood, $type)) {
            throw MaterialAlreadyExistsException::withWoodAndType($wood, $type);
        }

        $this->materialApplicationService->save(Material::create($wood, $type));
    }
}
