<?php

declare(strict_types=1);

namespace App\Application\Product\Command\DeleteProduct;

use App\Application\Product\Service\ProductApplicationService;
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
