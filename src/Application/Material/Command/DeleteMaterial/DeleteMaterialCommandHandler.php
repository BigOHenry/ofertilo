<?php

declare(strict_types=1);

namespace App\Application\Material\Command\DeleteMaterial;

use App\Application\Material\Service\MaterialApplicationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteMaterialCommandHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    public function __invoke(DeleteMaterialCommand $command): void
    {
        $color = $this->materialApplicationService->getById($command->getId());

        $this->materialApplicationService->delete($color);
    }
}
