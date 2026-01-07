<?php

declare(strict_types=1);

namespace App\Application\Product\Command\CreateProductSize;

use App\Application\Product\Service\ProductApplicationService;
use App\Domain\Product\Exception\ProductSizeValidationException;
use App\Domain\Product\Validator\ProductSizeValidator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateProductSizeCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
    ) {
    }

    public function __invoke(CreateProductSizeCommand $command): void
    {
        $product = $this->productApplicationService->getById($command->productId);
        $errors = ProductSizeValidator::validate($command->height, $command->width, $command->thickness);

        if (!empty($errors)) {
            throw ProductSizeValidationException::withErrors($errors);
        }

        $product->addProductSize($command->height, $command->width, $command->thickness);
        $this->productApplicationService->save($product);
    }
}
