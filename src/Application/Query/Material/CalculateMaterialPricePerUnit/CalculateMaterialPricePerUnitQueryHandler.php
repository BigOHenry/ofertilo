<?php

declare(strict_types=1);

namespace App\Application\Query\Material\CalculateMaterialPricePerUnit;

use App\Application\Exception\DeveloperLogicException;
use App\Application\Service\MaterialApplicationService;
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
        $material = $this->materialApplicationService->findById($query->getMaterialId());

        if ($material === null) {
            throw MaterialNotFoundException::withId($query->getMaterialId());
        }

        $lengthInMeters = $query->getLength() / 1000;
        $widthInMeters = $query->getWidth() / 1000;

        if ($material->getMeasurementType() === MeasurementType::VOLUME) {
            if ($query->getThickness() === null) {
                throw DeveloperLogicException::invalidArguments('Thickness is required for volume calculation');
            }

            $thicknessInMeters = $query->getThickness() / 1000;
            $volume = $lengthInMeters * $widthInMeters * $thicknessInMeters;

            return round($query->getPrice() / $volume, mode: \RoundingMode::AwayFromZero);
        }

        $area = $lengthInMeters * $widthInMeters;

        return round($query->getPrice() / $area, mode: \RoundingMode::AwayFromZero);
    }
}
