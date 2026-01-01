<?php

declare(strict_types=1);

namespace App\Application\Material\Command\EditMaterialPrice;

use App\Application\Material\Service\MaterialApplicationService;
use App\Domain\Material\Exception\MaterialPriceAlreadyExistsException;
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
        $material = $this->materialApplicationService->getById($command->materialId);
        $materialPrice = $material->getPriceById($command->priceId);

        $errors = MaterialPriceValidator::validate($command->thickness, (float) $command->price);

        if (!empty($errors)) {
            throw MaterialPriceValidationException::withErrors($errors);
        }

        $foundPrice = $material->findPriceByThickness($command->thickness);
        if ($foundPrice !== null && $foundPrice !== $materialPrice) {
            throw MaterialPriceAlreadyExistsException::withThickness($command->thickness);
        }

        $materialPrice->setPrice($command->price);
        $materialPrice->setThickness($command->thickness);

        $this->materialApplicationService->save($material);
    }
}
