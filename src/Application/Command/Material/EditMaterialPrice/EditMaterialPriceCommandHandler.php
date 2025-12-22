<?php

declare(strict_types=1);

namespace App\Application\Command\Material\EditMaterialPrice;

use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Exception\MaterialPriceAlreadyExistsException;
use App\Domain\Material\Exception\MaterialPriceNotFoundException;
use App\Domain\Material\Exception\MaterialPriceValidationException;
use App\Domain\Material\Validator\MaterialPriceValidator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditMaterialPriceCommandHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    public function __invoke(EditMaterialPriceCommand $command): void
    {
        $materialPrice = $this->materialApplicationService->findMaterialPriceById($command->getId());

        if ($materialPrice === null) {
            throw MaterialPriceNotFoundException::withId($command->getId());
        }

        $errors = MaterialPriceValidator::validate($command->getThickness(), (float) $command->getPrice());

        if (!empty($errors)) {
            throw MaterialPriceValidationException::withErrors($errors);
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
