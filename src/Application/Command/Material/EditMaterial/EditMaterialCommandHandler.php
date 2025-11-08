<?php

declare(strict_types=1);

namespace App\Application\Command\Material\EditMaterial;

use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Entity\Material;
use App\Domain\Material\Exception\MaterialAlreadyExistsException;
use App\Domain\Material\Exception\MaterialNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditMaterialCommandHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    public function __invoke(EditMaterialCommand $command): void
    {
        $wood = $command->getWood();
        $type = $command->getType();

        $foundMaterial = $this->materialApplicationService->findByWoodAndType($wood, $type);
        if ($foundMaterial !== null && $foundMaterial->getId() !== $command->getId()) {
            throw MaterialAlreadyExistsException::withWoodAndType($wood, $type);
        }

        $material = $this->materialApplicationService->findById($command->getId());

        if ($material === null) {
            throw MaterialNotFoundException::withId($command->getId());
        }

        $material->setWood($wood);
        $material->setType($type);
        $material->setEnabled($command->isEnabled());

        $this->materialApplicationService->save(Material::create($wood, $type));
    }
}
