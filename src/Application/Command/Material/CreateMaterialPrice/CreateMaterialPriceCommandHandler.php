<?php

declare(strict_types=1);

namespace App\Application\Command\Material\CreateMaterialPrice;

use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Exception\MaterialNotFoundException;
use App\Domain\Material\Exception\MaterialPriceAlreadyExistsException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateMaterialPriceCommandHandler
{
    public function __construct(
        private MaterialApplicationService $materialService,
    ) {
    }

    public function __invoke(CreateMaterialPriceCommand $command): void
    {
        $material = $this->materialService->findById($command->getMaterialId());

        if ($material === null) {
            throw MaterialNotFoundException::withId($command->getMaterialId());
        }

        $thickness = $command->getThickness();

        if ($this->materialService->findMaterialPriceByMaterialAndThickness($material, $thickness)) {
            throw MaterialPriceAlreadyExistsException::withThickness($thickness);
        }

        $material->addPrice($command->getThickness(), $command->getPrice());

        $this->materialService->save($material);
    }
}
