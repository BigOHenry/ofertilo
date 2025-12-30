<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Command\Material\CreateMaterial\CreateMaterialCommand;
use App\Application\Command\Material\CreateMaterial\CreateMaterialCommandHandler;
use App\Application\Service\MaterialApplicationService;
use App\Application\Service\WoodApplicationService;
use App\Domain\Material\Entity\Material;
use App\Domain\Material\Exception\MaterialAlreadyExistsException;
use App\Domain\Material\ValueObject\MaterialType;
use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Exception\WoodNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateMaterialCommandHandlerTest extends TestCase
{
    private MaterialApplicationService&MockObject $materialService;
    private WoodApplicationService&MockObject $woodService;
    private CreateMaterialCommandHandler $handler;

    protected function setUp(): void
    {
        $this->materialService = $this->createMock(MaterialApplicationService::class);
        $this->woodService = $this->createMock(WoodApplicationService::class);
        $this->handler = new CreateMaterialCommandHandler(
            $this->materialService,
            $this->woodService
        );
    }

    public function testHandleCreatesPieceMaterial(): void
    {
        $wood = Wood::create('oak');
        $command = new CreateMaterialCommand(
            woodId: 1,
            type: MaterialType::PIECE
        );

        $this->woodService
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->with($wood, MaterialType::PIECE)
            ->willReturn(null)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Material $material) {
                return $material->getType() === MaterialType::PIECE;
            }))
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleCreatesPlywoodMaterial(): void
    {
        $wood = Wood::create('oak');
        $command = new CreateMaterialCommand(
            woodId: 1,
            type: MaterialType::PLYWOOD
        );

        $this->woodService
            ->expects($this->once())
            ->method('findById')
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->willReturn(null)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Material $material) {
                return $material->getType() === MaterialType::PLYWOOD;
            }))
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleCreatesEdgeGluedPanelMaterial(): void
    {
        $wood = Wood::create('oak');
        $command = new CreateMaterialCommand(
            woodId: 1,
            type: MaterialType::EDGE_GLUED_PANEL
        );

        $this->woodService
            ->expects($this->once())
            ->method('findById')
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->willReturn(null)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Material $material) {
                return $material->getType() === MaterialType::EDGE_GLUED_PANEL;
            }))
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleCreatesSolidWoodMaterial(): void
    {
        $wood = Wood::create('oak');
        $command = new CreateMaterialCommand(
            woodId: 1,
            type: MaterialType::SOLID_WOOD
        );

        $this->woodService
            ->expects($this->once())
            ->method('findById')
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->willReturn(null)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Material $material) {
                return $material->getType() === MaterialType::SOLID_WOOD;
            }))
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenWoodNotFound(): void
    {
        $command = new CreateMaterialCommand(
            woodId: 999,
            type: MaterialType::PIECE
        );

        $this->woodService
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null)
        ;

        $this->expectException(WoodNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenMaterialAlreadyExists(): void
    {
        $wood = Wood::create('oak');
        $existingMaterial = $this->createMock(Material::class);

        $command = new CreateMaterialCommand(
            woodId: 1,
            type: MaterialType::PLYWOOD
        );

        $this->woodService
            ->expects($this->once())
            ->method('findById')
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->with($wood, MaterialType::PLYWOOD)
            ->willReturn($existingMaterial)
        ;

        $this->expectException(MaterialAlreadyExistsException::class);

        $this->handler->__invoke($command);
    }
}
