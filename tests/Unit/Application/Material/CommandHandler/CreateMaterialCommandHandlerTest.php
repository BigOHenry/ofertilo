<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Command\Material\CreateMaterial\CreateMaterialCommand;
use App\Application\Command\Material\CreateMaterial\CreateMaterialCommandHandler;
use App\Application\Service\MaterialApplicationService;
use App\Application\Service\WoodApplicationService;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Material\Exception\MaterialAlreadyExistsException;
use App\Domain\Material\ValueObject\MaterialType;
use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Exception\WoodNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
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
            ->method('getById')
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
            ->method('getById')
            ->with(1)
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->with($wood, MaterialType::PLYWOOD)
            ->willReturn(null)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
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
            ->method('getById')
            ->with(1)
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->with($wood, MaterialType::EDGE_GLUED_PANEL)
            ->willReturn(null)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
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
            ->method('getById')
            ->with(1)
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->with($wood, MaterialType::SOLID_WOOD)
            ->willReturn(null)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testHandleThrowsExceptionWhenWoodNotFound(): void
    {
        $command = new CreateMaterialCommand(
            woodId: 999,
            type: MaterialType::PLYWOOD
        );

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willThrowException(WoodNotFoundException::withId(999))
        ;

        $this->expectException(WoodNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenMaterialAlreadyExists(): void
    {
        $wood = Wood::create('oak');

        $command = new CreateMaterialCommand(
            woodId: 1,
            type: MaterialType::PLYWOOD
        );

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->with($wood, MaterialType::PLYWOOD)
            ->willReturn(PlywoodMaterial::create($wood))
        ;

        $this->expectException(MaterialAlreadyExistsException::class);

        $this->handler->__invoke($command);
    }
}
