<?php

declare(strict_types=1);

namespace App\Application\Product\Command\DeleteProductComponent;

use App\Application\Product\Service\ProductApplicationService;
use App\Domain\Product\Repository\ProductComponentRepositoryInterface;
use App\Infrastructure\Service\FileStorage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteProductComponentCommandHandler
{
    public function __construct(
        private ProductComponentRepositoryInterface $componentRepository,
        private ProductApplicationService $productService,
        private FileStorage $fileStorage
    ) {
    }

    public function __invoke(DeleteProductComponentCommand $command): void
    {
        $productComponent = $this->componentRepository->getById($command->productComponentId);
        $imageFile = $productComponent->getBlueprintFile();
        if ($imageFile && $this->fileStorage->exists($imageFile)) {
            $this->fileStorage->delete($imageFile);
        }

        $productComponent->getProductVariant()->removeProductComponent($productComponent);

        $this->productService->save($productComponent->getProductVariant()->getProduct());
    }
}
