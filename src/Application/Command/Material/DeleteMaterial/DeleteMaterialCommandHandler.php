<?php

declare(strict_types=1);

namespace App\Application\Command\Material\DeleteMaterial;

use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Exception\MaterialNotFoundException;
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
        $color = $this->materialApplicationService->findById($command->getId());

        if ($color === null) {
            throw MaterialNotFoundException::withId($command->getId());
        }

        $this->materialApplicationService->delete($color);
    }
}
