<?php

declare(strict_types=1);

namespace App\Application\Command\Material\CreateMaterialPrice;

use App\Application\Service\MaterialApplicationService;
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
        $material = $this->materialService->getById($command->getMaterialId());

        $errors = MaterialPriceValidator::validate($command->getThickness(), (float) $command->getPrice());

        if (!empty($errors)) {
            throw MaterialPriceValidationException::withErrors($errors);
        }

        $material->addPrice($command->getThickness(), $command->getPrice());

        $this->materialService->save($material);
    }
}
