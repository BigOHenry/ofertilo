<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Material\Command\CreateMaterial\CreateMaterialCommand;
use App\Application\Material\Command\CreateMaterial\CreateMaterialCommandHandler;
use App\Application\Material\Service\MaterialApplicationService;
use App\Application\Wood\Service\WoodApplicationService;
use App\Domain\Material\Entity\EdgeGluedPanelMaterial;
use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\PieceMaterial;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Material\Entity\SolidWoodMaterial;
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
        $woodId = $wood->getId();

        $command = new CreateMaterialCommand(
            woodId: $woodId,
            type: MaterialType::PIECE
        );

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with($woodId)
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->with($wood, MaterialType::PIECE)
            ->willReturn(null)
        ;

        $savedMaterial = null;
        $this->materialService
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Material $material) use (&$savedMaterial): void {
                $savedMaterial = $material;
            })
        ;

        $this->handler->__invoke($command);

        $this->assertInstanceOf(PieceMaterial::class, $savedMaterial);
        $this->assertSame($wood, $savedMaterial->getWood());
    }

    public function testHandleCreatesPlywoodMaterial(): void
    {
        $wood = Wood::create('oak');
        $woodId = $wood->getId();

        $command = new CreateMaterialCommand(
            woodId: $woodId,
            type: MaterialType::PLYWOOD
        );

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with($woodId)
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->with($wood, MaterialType::PLYWOOD)
            ->willReturn(null)
        ;

        $savedMaterial = null;
        $this->materialService
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Material $material) use (&$savedMaterial): void {
                $savedMaterial = $material;
            })
        ;

        $this->handler->__invoke($command);

        $this->assertInstanceOf(PlywoodMaterial::class, $savedMaterial);
        $this->assertSame($wood, $savedMaterial->getWood());
    }

    public function testHandleCreatesEdgeGluedPanelMaterial(): void
    {
        $wood = Wood::create('oak');
        $woodId = $wood->getId();

        $command = new CreateMaterialCommand(
            woodId: $woodId,
            type: MaterialType::EDGE_GLUED_PANEL
        );

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with($woodId)
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->with($wood, MaterialType::EDGE_GLUED_PANEL)
            ->willReturn(null)
        ;

        $savedMaterial = null;
        $this->materialService
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Material $material) use (&$savedMaterial): void {
                $savedMaterial = $material;
            })
        ;

        $this->handler->__invoke($command);

        $this->assertInstanceOf(EdgeGluedPanelMaterial::class, $savedMaterial);
        $this->assertSame($wood, $savedMaterial->getWood());
    }

    public function testHandleCreatesSolidWoodMaterial(): void
    {
        $wood = Wood::create('oak');
        $woodId = $wood->getId();

        $command = new CreateMaterialCommand(
            woodId: $woodId,
            type: MaterialType::SOLID_WOOD
        );

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with($woodId)
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->with($wood, MaterialType::SOLID_WOOD)
            ->willReturn(null)
        ;

        $savedMaterial = null;
        $this->materialService
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Material $material) use (&$savedMaterial): void {
                $savedMaterial = $material;
            })
        ;

        $this->handler->__invoke($command);

        $this->assertInstanceOf(SolidWoodMaterial::class, $savedMaterial);
        $this->assertSame($wood, $savedMaterial->getWood());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testHandleThrowsExceptionWhenWoodNotFound(): void
    {
        $nonExistentWoodId = '00000000-0000-0000-0000-000000000999';

        $command = new CreateMaterialCommand(
            woodId: $nonExistentWoodId,
            type: MaterialType::PLYWOOD
        );

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with($nonExistentWoodId)
            ->willThrowException(WoodNotFoundException::withId($nonExistentWoodId))
        ;

        $this->expectException(WoodNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenMaterialAlreadyExists(): void
    {
        $wood = Wood::create('oak');
        $woodId = $wood->getId();

        $command = new CreateMaterialCommand(
            woodId: $woodId,
            type: MaterialType::PLYWOOD
        );

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with($woodId)
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

    public function testHandleCreatedMaterialHasUuid(): void
    {
        $wood = Wood::create('oak');
        $woodId = $wood->getId();

        $command = new CreateMaterialCommand(
            woodId: $woodId,
            type: MaterialType::PLYWOOD
        );

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with($woodId)
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->willReturn(null)
        ;

        $savedMaterial = null;
        $this->materialService
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Material $material) use (&$savedMaterial): void {
                $savedMaterial = $material;
            })
        ;

        $this->handler->__invoke($command);

        $this->assertNotNull($savedMaterial);
        $materialId = $savedMaterial->getId();
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $materialId,
            'Material ID should be a valid UUID v4'
        );
    }

    public function testHandleCreatedMaterialIsEnabledByDefault(): void
    {
        $wood = Wood::create('oak');
        $woodId = $wood->getId();

        $command = new CreateMaterialCommand(
            woodId: $woodId,
            type: MaterialType::PLYWOOD
        );

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->willReturn(null)
        ;

        $savedMaterial = null;
        $this->materialService
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Material $material) use (&$savedMaterial): void {
                $savedMaterial = $material;
            })
        ;

        $this->handler->__invoke($command);

        $this->assertNotNull($savedMaterial);
        $this->assertTrue($savedMaterial->isEnabled());
    }
}
