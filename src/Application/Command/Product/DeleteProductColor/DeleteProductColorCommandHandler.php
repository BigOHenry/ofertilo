<?php

declare(strict_types=1);

namespace App\Application\Command\Product\DeleteProductColor;

use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Exception\MaterialPriceNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteProductColorCommandHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    public function __invoke(DeleteProductColorCommand $command): void
    {
        $materialPrice = $this->materialApplicationService->findMaterialPriceById($command->getId());

        if ($materialPrice === null) {
            throw MaterialPriceNotFoundException::withId($command->getId());
        }

        $this->materialApplicationService->deleteMaterialPrice($materialPrice);
    }
}
