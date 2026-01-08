<?php

declare(strict_types=1);

namespace App\Application\Product\Command\CreateProductVariantComponent;

use App\Application\Product\Service\ProductApplicationService;
use App\Domain\Product\Entity\ProductComponent;
use App\Domain\Product\Exception\ProductVariantValidationException;
use App\Domain\Product\Repository\ProductVariantRepositoryInterface;
use App\Domain\Product\Validator\ProductComponentValidator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateProductVariantComponentCommandHandler
{
    public function __construct(
        private ProductVariantRepositoryInterface $repository,
        private ProductApplicationService $productService
    ) {
    }

    public function __invoke(CreateProductVariantComponentCommand $command): void
    {
        $productVariant = $this->repository->getById($command->productVariantId);

        $errors = ProductComponentValidator::validate($command->quantity, $command->length, $command->width, $command->thickness, $command->blueprintFile);

        if (!empty($errors)) {
            throw ProductVariantValidationException::withErrors($errors);
        }

        $productComponent = ProductComponent::create($productVariant, $command->quantity, $command->length, $command->width, $command->thickness, $command->shapeDescription);

        $productVariant->addProductComponent($productComponent);

        $this->productService->save($productVariant->getProduct());
    }
}
