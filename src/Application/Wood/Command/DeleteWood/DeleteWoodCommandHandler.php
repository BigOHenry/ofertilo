<?php

declare(strict_types=1);

namespace App\Application\Wood\Command\DeleteWood;

use App\Application\Wood\Service\WoodApplicationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteWoodCommandHandler
{
    public function __construct(
        private WoodApplicationService $woodApplicationService,
    ) {
    }

    public function __invoke(DeleteWoodCommand $command): void
    {
        $wood = $this->woodApplicationService->getById($command->getId());

        $this->woodApplicationService->delete($wood);
    }
}
