<?php

declare(strict_types=1);

namespace App\Application\Material\Command\EditMaterial;

use App\Application\Material\Service\MaterialApplicationService;
use App\Application\Wood\Service\WoodApplicationService;
use App\Domain\Material\Exception\MaterialAlreadyExistsException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditMaterialCommandHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
        private WoodApplicationService $woodService,
    ) {
    }

    public function __invoke(EditMaterialCommand $command): void
    {
        $material = $this->materialApplicationService->getById($command->materialId);
        $wood = $this->woodService->getById($command->woodId);

        $foundMaterial = $this->materialApplicationService->findByWoodAndType($wood, $material->getType());
        if ($foundMaterial !== null && $foundMaterial->getId() !== $command->materialId) {
            throw MaterialAlreadyExistsException::withWoodAndType($wood, $material->getType());
        }

        $material->setWood($wood);
        $material->setEnabled($command->enabled);

        $this->materialApplicationService->save($material);
    }
}
