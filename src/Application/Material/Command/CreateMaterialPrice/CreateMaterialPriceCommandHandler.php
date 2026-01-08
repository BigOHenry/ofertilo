<?php

declare(strict_types=1);

namespace App\Application\Material\Command\CreateMaterialPrice;

use App\Application\Material\Service\MaterialApplicationService;
use App\Domain\Material\Exception\MaterialPriceValidationException;
use App\Domain\Material\Validator\MaterialPriceValidator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateMaterialPriceCommandHandler
{
    public function __construct(
        private MaterialApplicationService $materialService,
    ) {
    }

    public function __invoke(CreateMaterialPriceCommand $command): void
    {
        $material = $this->materialService->getById($command->materialId);

        $errors = MaterialPriceValidator::validate($command->thickness, (float) $command->price);

        if (!empty($errors)) {
            throw MaterialPriceValidationException::withErrors($errors);
        }

        $material->addPrice($command->thickness, $command->price);

        $this->materialService->save($material);
    }
}
