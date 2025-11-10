<?php

declare(strict_types=1);

namespace App\Application\Command\Product\CreateProductColor;

use App\Application\Service\ProductApplicationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateProductColorCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    public function __invoke(CreateProductColorCommand $command): void
    {
        $command->getProduct()->addColor($command->getColor(), $command->getDescription());
        $this->productApplicationService->save($command->getProduct());
    }
}
