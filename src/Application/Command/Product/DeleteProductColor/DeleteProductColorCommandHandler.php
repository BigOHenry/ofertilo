<?php

declare(strict_types=1);

namespace App\Application\Command\Product\DeleteProductColor;

use App\Application\Service\ProductApplicationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteProductColorCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    public function __invoke(DeleteProductColorCommand $command): void
    {
        $product = $this->productApplicationService->getById($command->productId);
        $productColor = $product->getProductColorById($command->productColorId);

        $product->removeProductColor($productColor);

        $this->productApplicationService->save($productColor->getProduct());
    }
}
