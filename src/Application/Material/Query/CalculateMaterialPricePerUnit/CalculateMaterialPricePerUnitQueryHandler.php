<?php

declare(strict_types=1);

namespace App\Application\Material\Query\CalculateMaterialPricePerUnit;

use App\Application\Material\Service\MaterialApplicationService;
use App\Application\Shared\Exception\DeveloperLogicException;
use App\Domain\Material\Exception\MaterialNotFoundException;
use App\Domain\Material\ValueObject\MeasurementType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CalculateMaterialPricePerUnitQueryHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    public function __invoke(CalculateMaterialPricePerUnitQuery $query): float
    {
        $material = $this->materialApplicationService->findById($query->materialId);

        if ($material === null) {
            throw MaterialNotFoundException::withId($query->materialId);
        }

        $lengthInMeters = $query->length / 1000;
        $widthInMeters = $query->width / 1000;

        if ($material->getMeasurementType() === MeasurementType::VOLUME) {
            if ($query->thickness === null) {
                throw DeveloperLogicException::invalidArguments('Thickness is required for volume calculation');
            }

            $thicknessInMeters = $query->thickness / 1000;
            $volume = $lengthInMeters * $widthInMeters * $thicknessInMeters;

            return round($query->price / $volume, mode: \RoundingMode::AwayFromZero);
        }

        $area = $lengthInMeters * $widthInMeters;

        return round($query->price / $area, mode: \RoundingMode::AwayFromZero);
    }
}
