<?php

declare(strict_types=1);

namespace App\Application\Product\Command\CreateProductVariant;

use App\Application\Product\Service\ProductApplicationService;
use App\Domain\Product\Exception\ProductVariantValidationException;
use App\Domain\Product\Validator\ProductVariantValidator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateProductVariantCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    public function __invoke(CreateProductVariantCommand $command): void
    {
        $product = $this->productApplicationService->getById($command->productId);
        $errors = ProductVariantValidator::validate($command->height, $command->width, $command->thickness);

        if (!empty($errors)) {
            throw ProductVariantValidationException::withErrors($errors);
        }

        $product->addProductVariant($command->height, $command->width, $command->thickness);
        $this->productApplicationService->save($product);
    }
}
