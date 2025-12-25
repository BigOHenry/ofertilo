<?php

declare(strict_types=1);

namespace App\Application\Command\Material\EditMaterial;

use App\Application\Service\MaterialApplicationService;
use App\Application\Service\WoodApplicationService;
use App\Domain\Material\Exception\MaterialAlreadyExistsException;
use App\Domain\Material\Exception\MaterialNotFoundException;
use App\Domain\Wood\Exception\WoodNotFoundException;
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
        $material = $this->materialApplicationService->findById($command->getId());

        if ($material === null) {
            throw MaterialNotFoundException::withId($command->getId());
        }

        $wood = $this->woodService->findById($command->getWoodId());

        if ($wood === null) {
            throw WoodNotFoundException::withId($command->getWoodId());
        }

        $foundMaterial = $this->materialApplicationService->findByWoodAndType($wood, $material->getType());
        if ($foundMaterial !== null && $foundMaterial->getId() !== $command->getId()) {
            throw MaterialAlreadyExistsException::withWoodAndType($wood, $material->getType());
        }

        $material->setWood($wood);
        $material->setEnabled($command->isEnabled());

        $this->materialApplicationService->save($material);
    }
}
