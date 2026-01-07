<?php

declare(strict_types=1);

namespace App\Application\Product\Command\DeleteProductSize;

use App\Application\Product\Service\ProductApplicationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteProductSizeCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    public function __invoke(DeleteProductSizeCommand $command): void
    {
        $product = $this->productApplicationService->getById($command->productId);
        $productSize = $product->getProductSizeById($command->productSizeId);

        $product->removeProductSize($productSize);

        $this->productApplicationService->save($productSize->getProduct());
    }
}
