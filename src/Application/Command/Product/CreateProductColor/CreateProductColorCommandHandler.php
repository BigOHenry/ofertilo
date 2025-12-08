<?php

declare(strict_types=1);

namespace App\Application\Command\Product\CreateProductColor;

use App\Application\Service\ColorApplicationService;
use App\Application\Service\ProductApplicationService;
use App\Domain\Color\Exception\ColorNotFoundException;
use App\Domain\Product\Exception\ProductNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateProductColorCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
        private ColorApplicationService $colorApplicationService,
    ) {
    }

    public function __invoke(CreateProductColorCommand $command): void
    {
        $product = $this->productApplicationService->findById($command->getProductId());

        if ($product === null) {
            throw ProductNotFoundException::withId($command->getProductId());
        }

        $color = $this->colorApplicationService->findById($command->getColorId());

        if ($color === null) {
            throw ColorNotFoundException::withId($command->getColorId());
        }

        $product->addColor($color, $command->getDescription());
        $this->productApplicationService->save($product);
    }
}
