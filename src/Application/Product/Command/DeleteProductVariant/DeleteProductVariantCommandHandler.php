<?php

declare(strict_types=1);

namespace App\Application\Product\Command\DeleteProductVariant;

use App\Application\Product\Service\ProductApplicationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteProductVariantCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    public function __invoke(DeleteProductVariantCommand $command): void
    {
        $product = $this->productApplicationService->getById($command->productId);
        $productVariant = $product->getProductVariantById($command->productVariantId);

        $product->removeProductVariant($productVariant);

        $this->productApplicationService->save($productVariant->getProduct());
    }
}
