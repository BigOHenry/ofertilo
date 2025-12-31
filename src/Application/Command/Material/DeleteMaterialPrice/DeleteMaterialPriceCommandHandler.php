<?php

declare(strict_types=1);

namespace App\Application\Command\Material\DeleteMaterialPrice;

use App\Application\Service\MaterialApplicationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteMaterialPriceCommandHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    public function __invoke(DeleteMaterialPriceCommand $command): void
    {
        $material = $this->materialApplicationService->getById($command->getMaterialId());
        $materialPrice = $material->getPriceById($command->getPriceId());

        $material->removePrice($materialPrice);

        $this->materialApplicationService->save($material);
    }
}
