<?php

declare(strict_types=1);

namespace App\Application\Command\Product\DeleteProductColor;

use App\Application\Service\ProductApplicationService;
use App\Domain\Product\Exception\ProductColorNotFoundException;
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
        $productColor = $this->productApplicationService->findProductColorById($command->getId());

        if ($productColor === null) {
            throw ProductColorNotFoundException::withId($command->getId());
        }

        $productColor->getProduct()->removeColor($productColor->getColor());

        $this->productApplicationService->save($productColor->getProduct());
    }
}
