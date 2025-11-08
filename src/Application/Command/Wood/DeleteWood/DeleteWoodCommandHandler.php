<?php

declare(strict_types=1);

namespace App\Application\Command\Wood\DeleteWood;

use App\Application\Service\WoodApplicationService;
use App\Domain\Wood\Exception\WoodNotFoundException;
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
        $wood = $this->woodApplicationService->findById($command->getId());

        if ($wood === null) {
            throw WoodNotFoundException::withId($command->getId());
        }

        $this->woodApplicationService->delete($wood);
    }
}
