<?php

declare(strict_types=1);

namespace App\Application\Command\Color\DeleteColor;

use App\Application\Service\ColorApplicationService;
use App\Domain\Color\Exception\ColorNotFoundException;
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
        $color = $this->colorApplicationService->findById($command->getId());

        if ($color === null) {
            throw ColorNotFoundException::withId($command->getId());
        }

        $this->colorApplicationService->delete($color);
    }
}
