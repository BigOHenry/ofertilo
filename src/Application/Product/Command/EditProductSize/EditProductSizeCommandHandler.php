<?php

declare(strict_types=1);

namespace App\Application\Product\Command\EditProductSize;

use App\Application\Product\Service\ProductApplicationService;
use App\Domain\Product\Exception\ProductSizeAlreadyExistsException;
use App\Domain\Product\Exception\ProductSizeValidationException;
use App\Domain\Product\Validator\ProductSizeValidator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditProductSizeCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    public function __invoke(EditProductSizeCommand $command): void
    {
        $product = $this->productApplicationService->getById($command->productId);
        $productSize = $product->getProductSizeById($command->productSizeId);

        $errors = ProductSizeValidator::validate($command->height, $command->width, $command->thickness);

        if (!empty($errors)) {
            throw ProductSizeValidationException::withErrors($errors);
        }

        $foundProductSize = $product->findProductSizeByDimensions($command->height, $command->width, $command->thickness);

        if ($foundProductSize !== null && $foundProductSize->getId() !== $productSize->getId()) {
            throw ProductSizeAlreadyExistsException::withDimensions($command->height, $command->width, $command->thickness);
        }

        $productSize->setHeight($command->height);
        $productSize->setWidth($command->width);
        $productSize->setThickness($command->thickness);

        $this->productApplicationService->save($product);
    }
}
