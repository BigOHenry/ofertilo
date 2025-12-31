<?php

declare(strict_types=1);

namespace App\Application\Command\Product\DeleteProduct;

use App\Application\Service\ProductApplicationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteProductCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    public function __invoke(DeleteProductCommand $command): void
    {
        $product = $this->productApplicationService->getById($command->getId());

        $this->productApplicationService->delete($product);
    }
}
