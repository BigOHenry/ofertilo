<?php

declare(strict_types=1);

namespace App\Application\Color\Command\DeleteColor;

use App\Application\Color\Service\ColorApplicationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteColorCommandHandler
{
    public function __construct(
        private ColorApplicationService $colorApplicationService,
    ) {
    }

    public function __invoke(DeleteColorCommand $command): void
    {
        $color = $this->colorApplicationService->getById($command->getId());

        $this->colorApplicationService->delete($color);
    }
}
