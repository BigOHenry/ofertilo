<?php

declare(strict_types=1);

namespace App\Application\Command\Product\DeleteProduct;

use App\Application\Service\ProductApplicationService;
use App\Domain\Product\Exception\ProductNotFoundException;
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
        $product = $this->productApplicationService->findById($command->getId());

        if ($product === null) {
            throw ProductNotFoundException::withId($command->getId());
        }

        $this->productApplicationService->delete($product);
    }
}
