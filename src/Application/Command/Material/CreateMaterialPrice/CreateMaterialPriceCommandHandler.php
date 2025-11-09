<?php

declare(strict_types=1);

namespace App\Application\Command\Material\CreateMaterialPrice;

use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Exception\MaterialPriceAlreadyExistsException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateMaterialPriceCommandHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    public function __invoke(CreateMaterialPriceCommand $command): void
    {
        $material = $command->getMaterial();
        $thickness = $command->getThickness();

        if ($this->materialApplicationService->findMaterialPriceByMaterialAndThickness($material, $thickness)) {
            throw MaterialPriceAlreadyExistsException::withThickness($thickness);
        }

        $material->addPrice($command->getThickness(), $command->getPrice());

        $this->materialApplicationService->save($material);
    }
}
