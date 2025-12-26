<?php

declare(strict_types=1);

namespace App\Application\Command\Product\EditProductColor;

use App\Application\Service\ColorApplicationService;
use App\Application\Service\ProductApplicationService;
use App\Domain\Color\Exception\ColorNotFoundException;
use App\Domain\Product\Exception\ProductColorNotFoundException;
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
        $color = $this->colorApplicationService->findById($command->getColorId());

        if ($color === null) {
            throw ColorNotFoundException::withId($command->getColorId());
        }

        $productColor = $this->productApplicationService->findProductColorById($command->getId());

        if ($productColor === null) {
            throw ProductColorNotFoundException::withId($command->getId());
        }

        $errors = ProductColorValidator::validate($command->getDescription());

        if (!empty($errors)) {
            throw WoodValidationException::withErrors($errors);
        }

        $productColor->getProduct()->updateColor($productColor, $color, $command->getDescription());

        $this->productApplicationService->save($productColor->getProduct());
    }
}
