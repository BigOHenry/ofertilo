<?php

declare(strict_types=1);

namespace App\Application\Command\Product\EditProductColor;

use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Exception\MaterialPriceAlreadyExistsException;
use App\Domain\Material\Exception\MaterialPriceNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditProductColorCommandHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    public function __invoke(EditProductColorCommand $command): void
    {
        $materialPrice = $this->materialApplicationService->findMaterialPriceById($command->getId());

        if ($materialPrice === null) {
            throw MaterialPriceNotFoundException::withId($command->getId());
        }

        $foundMaterial = $this->materialApplicationService->findMaterialPriceByMaterialAndThickness(
            $materialPrice->getMaterial(),
            $command->getThickness()
        );
        if ($foundMaterial !== null && $foundMaterial->getId() !== $materialPrice->getId()) {
            throw MaterialPriceAlreadyExistsException::withThickness($command->getThickness());
        }

        $materialPrice->setPrice($command->getPrice());
        $materialPrice->setThickness($command->getThickness());

        $this->materialApplicationService->saveMaterialPrice($materialPrice);
    }
}
