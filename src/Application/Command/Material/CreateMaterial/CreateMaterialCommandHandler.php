<?php

declare(strict_types=1);

namespace App\Application\Command\Material\CreateMaterial;

use App\Application\Service\MaterialApplicationService;
use App\Application\Service\WoodApplicationService;
use App\Domain\Material\Entity\Material;
use App\Domain\Material\Exception\MaterialAlreadyExistsException;
use App\Domain\Wood\Exception\WoodNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateMaterialCommandHandler
{
    public function __construct(
        private MaterialApplicationService $materialService,
        private WoodApplicationService $woodService,
    ) {
    }

    public function __invoke(CreateMaterialCommand $command): void
    {
        $wood = $this->woodService->findById($command->getWoodId());

        if ($wood === null) {
            throw WoodNotFoundException::withId($command->getWoodId());
        }

        $type = $command->getType();

        if ($this->materialService->findByWoodAndType($wood, $type)) {
            throw MaterialAlreadyExistsException::withWoodAndType($wood, $type);
        }

        $this->materialService->save(Material::create($wood, $type));
    }
}
