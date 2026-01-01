<?php

declare(strict_types=1);

namespace App\Application\Product\Command\CreateProductColor;

use App\Application\Color\Service\ColorApplicationService;
use App\Application\Product\Service\ProductApplicationService;
use App\Domain\Product\Validator\ProductColorValidator;
use App\Domain\Wood\Exception\WoodValidationException;
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
        $product = $this->productApplicationService->getById($command->getProductId());
        $color = $this->colorApplicationService->getById($command->getColorId());

        $errors = ProductColorValidator::validate($command->getDescription());

        if (!empty($errors)) {
            throw WoodValidationException::withErrors($errors);
        }

        $product->addColor($color, $command->getDescription());
        $this->productApplicationService->save($product);
    }
}
