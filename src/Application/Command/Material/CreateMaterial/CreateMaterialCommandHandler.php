<?php

declare(strict_types=1);

namespace App\Application\Command\Material\CreateMaterial;

use App\Application\Service\MaterialApplicationService;
use App\Application\Service\WoodApplicationService;
use App\Domain\Material\Entity\EdgeGluedPanelMaterial;
use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\PieceMaterial;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Material\Entity\SolidWoodMaterial;
use App\Domain\Material\Exception\MaterialAlreadyExistsException;
use App\Domain\Material\ValueObject\MaterialType;
use App\Domain\Wood\Entity\Wood;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateMaterialCommandHandler
{
    public function __construct(
        private MaterialApplicationService $materialService,
        private WoodApplicationService $woodService,
    ) {
    }

    public function __invoke(CreateMaterialCommand $command): void
    {
        $wood = $this->woodService->getById($command->getWoodId());

        $type = $command->getType();

        if ($this->materialService->findByWoodAndType($wood, $type)) {
            throw MaterialAlreadyExistsException::withWoodAndType($wood, $type);
        }

        $this->materialService->save($this->createMaterialByType($type, $wood));
    }

    private function createMaterialByType(MaterialType $type, Wood $wood): Material
    {
        return match ($type) {
            MaterialType::PIECE => PieceMaterial::create($wood),
            MaterialType::PLYWOOD => PlywoodMaterial::create($wood),
            MaterialType::EDGE_GLUED_PANEL => EdgeGluedPanelMaterial::create($wood),
            MaterialType::SOLID_WOOD => SolidWoodMaterial::create($wood),
        };
    }
}
