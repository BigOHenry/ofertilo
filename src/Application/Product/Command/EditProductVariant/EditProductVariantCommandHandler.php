<?php

declare(strict_types=1);

namespace App\Application\Product\Command\EditProductVariant;

use App\Application\Product\Service\ProductApplicationService;
use App\Domain\Product\Exception\ProductVariantAlreadyExistsException;
use App\Domain\Product\Exception\ProductVariantValidationException;
use App\Domain\Product\Validator\ProductVariantValidator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditProductVariantCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    public function __invoke(EditProductVariantCommand $command): void
    {
        $product = $this->productApplicationService->getById($command->productId);
        $productVariant = $product->getProductVariantById($command->productVariantId);

        $errors = ProductVariantValidator::validate($command->height, $command->width, $command->thickness);

        if (!empty($errors)) {
            throw ProductVariantValidationException::withErrors($errors);
        }

        $foundProductVariant = $product->findProductVariantByDimensions($command->height, $command->width, $command->thickness);

        if ($foundProductVariant !== null && $foundProductVariant->getId() !== $productVariant->getId()) {
            throw ProductVariantAlreadyExistsException::withDimensions($command->height, $command->width, $command->thickness);
        }

        $productVariant->setHeight($command->height);
        $productVariant->setWidth($command->width);
        $productVariant->setThickness($command->thickness);

        $this->productApplicationService->save($product);
    }
}
