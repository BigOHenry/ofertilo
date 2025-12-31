<?php

declare(strict_types=1);

namespace App\Application\Command\Product\EditProductColor;

use App\Application\Service\ColorApplicationService;
use App\Application\Service\ProductApplicationService;
use App\Domain\Product\Exception\ProductColorAlreadyExistsException;
use App\Domain\Product\Validator\ProductColorValidator;
use App\Domain\Wood\Exception\WoodValidationException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditProductColorCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
        private ColorApplicationService $colorApplicationService,
    ) {
    }

    public function __invoke(EditProductColorCommand $command): void
    {
        $product = $this->productApplicationService->getById($command->productId);
        $productColor = $product->getProductColorById($command->productColorId);
        $color = $this->colorApplicationService->getById($command->colorId);

        $errors = ProductColorValidator::validate($command->description);

        if (!empty($errors)) {
            throw WoodValidationException::withErrors($errors);
        }

        $foundProductColor = $product->findProductColorByColor($color);
        if ($foundProductColor !== null && $foundProductColor !== $productColor) {
            throw ProductColorAlreadyExistsException::withCode($color->getCode());
        }

        $productColor->setColor($color);
        $productColor->setDescription($command->description);

        $this->productApplicationService->save($product);
    }
}
