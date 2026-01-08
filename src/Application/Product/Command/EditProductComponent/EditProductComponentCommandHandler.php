<?php

declare(strict_types=1);

namespace App\Application\Product\Command\EditProductComponent;

use App\Application\Product\Service\ProductApplicationService;
use App\Domain\Product\Exception\ProductVariantValidationException;
use App\Domain\Product\Repository\ProductComponentRepositoryInterface;
use App\Domain\Product\Validator\ProductComponentValidator;
use App\Domain\Shared\File\Entity\File;
use App\Domain\Shared\File\ValueObject\FileType;
use App\Infrastructure\Service\FileStorage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditProductComponentCommandHandler
{
    public function __construct(
        private ProductComponentRepositoryInterface $repository,
        private ProductApplicationService $productService,
        private FileStorage $fileStorage,
    ) {
    }

    public function __invoke(EditProductComponentCommand $command): void
    {
        $productComponent = $this->repository->getById($command->productComponentId);

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

        $productComponent->setQuantity($command->quantity);
        $productComponent->setLength($command->length);
        $productComponent->setWidth($command->width);
        $productComponent->setThickness($command->thickness);
        $productComponent->setShapeDescription($command->shapeDescription);

        if ($command->blueprintFile) {
            $oldBlueprintFile = $productComponent->getBlueprintFile();

            // Delete old blueprint if exists
            if ($oldBlueprintFile && $this->fileStorage->exists($oldBlueprintFile)) {
                $this->fileStorage->delete($oldBlueprintFile);
            }

            // Create and set new File entity
            $newBlueprintFile = File::createFromUploadedFile(
                uploadedFile: $command->blueprintFile,
                type: FileType::IMAGE
            );

            $productComponent->setBlueprintFile($newBlueprintFile);
            $this->fileStorage->store($newBlueprintFile, $command->blueprintFile);
        }

        $this->productService->save($productComponent->getProductVariant()->getProduct());
    }
}
