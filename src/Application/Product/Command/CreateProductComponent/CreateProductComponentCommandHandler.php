<?php

declare(strict_types=1);

namespace App\Application\Product\Command\CreateProductComponent;

use App\Application\Product\Service\ProductApplicationService;
use App\Domain\Product\Entity\ProductComponent;
use App\Domain\Product\Exception\ProductVariantValidationException;
use App\Domain\Product\Repository\ProductVariantRepositoryInterface;
use App\Domain\Product\Validator\ProductComponentValidator;
use App\Domain\Shared\File\Entity\File;
use App\Domain\Shared\File\ValueObject\FileType;
use App\Infrastructure\Service\FileStorage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateProductComponentCommandHandler
{
    public function __construct(
        private ProductVariantRepositoryInterface $repository,
        private ProductApplicationService $productService,
        private FileStorage $fileStorage,
    ) {
    }

    public function __invoke(CreateProductComponentCommand $command): void
    {
        $productVariant = $this->repository->getById($command->productVariantId);

        $errors = ProductComponentValidator::validate(
            $command->quantity,
            $command->length,
            $command->width,
            $command->thickness,
            $command->blueprintFile
        );

        if (!empty($errors)) {
            throw ProductVariantValidationException::withErrors($errors);
        }

        $productComponent = ProductComponent::create(
            $productVariant,
            $command->quantity,
            $command->length,
            $command->width,
            $command->thickness,
            $command->shapeDescription
        );

        $productVariant->addProductComponent($productComponent);

        if ($command->blueprintFile) {
            $newBlueprintFile = File::createFromUploadedFile(
                uploadedFile: $command->blueprintFile,
                type: FileType::IMAGE
            );

            $productComponent->setBlueprintFile($newBlueprintFile);
            $this->fileStorage->store($newBlueprintFile, $command->blueprintFile);
        }

        $this->productService->save($productVariant->getProduct());
    }
}
