<?php

declare(strict_types=1);

namespace App\Application\Command\Product\EditProductColor;

use App\Application\Service\ProductApplicationService;
use App\Domain\Product\Exception\ProductColorNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditProductColorCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    public function __invoke(EditProductColorCommand $command): void
    {
        $productColor = $this->productApplicationService->findProductColorById($command->getId());

        if ($productColor === null) {
            throw ProductColorNotFoundException::withId($command->getId());
        }

        $productColor->getProduct()->updateColor($productColor, $command->getColor(), $command->getDescription());

        $this->productApplicationService->save($productColor->getProduct());
    }
}
