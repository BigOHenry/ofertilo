<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Material\Command\EditMaterial\EditMaterialCommand;
use App\Application\Material\Command\EditMaterial\EditMaterialCommandHandler;
use App\Application\Material\Service\MaterialApplicationService;
use App\Application\Wood\Service\WoodApplicationService;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Material\Exception\MaterialAlreadyExistsException;
use App\Domain\Material\Exception\MaterialNotFoundException;
use App\Domain\Material\ValueObject\MaterialType;
use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Exception\WoodNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EditMaterialCommandHandlerTest extends TestCase
{
    private MaterialApplicationService&MockObject $materialService;
    private WoodApplicationService&MockObject $woodService;
    private EditMaterialCommandHandler $handler;

    protected function setUp(): void
    {
        $this->materialService = $this->createMock(MaterialApplicationService::class);
        $this->woodService = $this->createMock(WoodApplicationService::class);
        $this->handler = new EditMaterialCommandHandler(
            $this->materialService,
            $this->woodService
        );
    }

    public function testHandleUpdatesMaterialSuccessfully(): void
    {
        $wood = Wood::create('oak');
        $newWood = Wood::create('pine');
        $material = PlywoodMaterial::create($wood);

        $materialId = $material->getId();
        $newWoodId = $newWood->getId();

        $command = new EditMaterialCommand(
            materialId: $materialId,
            woodId: $newWoodId,
            enabled: false
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with($newWoodId)
            ->willReturn($newWood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->willReturn(null)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);

        $this->assertSame($newWood, $material->getWood());
        $this->assertFalse($material->isEnabled());
    }

    public function testHandleThrowsExceptionWhenMaterialNotFound(): void
    {
        $woodId = '00000000-0000-0000-0000-000000000001';
        $nonExistentMaterialId = '00000000-0000-0000-0000-000000000999';

        $command = new EditMaterialCommand(
            materialId: $nonExistentMaterialId,
            woodId: $woodId,
            enabled: true
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($nonExistentMaterialId)
            ->willThrowException(MaterialNotFoundException::withId($nonExistentMaterialId))
        ;

        $this->woodService
            ->expects($this->never())
            ->method('getById')
        ;

        $this->expectException(MaterialNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenWoodNotFound(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $materialId = $material->getId();
        $nonExistentWoodId = '00000000-0000-0000-0000-000000000999';

        $command = new EditMaterialCommand(
            materialId: $materialId,
            woodId: $nonExistentWoodId,
            enabled: true
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with($nonExistentWoodId)
            ->willThrowException(WoodNotFoundException::withId($nonExistentWoodId))
        ;

        $this->expectException(WoodNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenMaterialWithSameWoodAndTypeExists(): void
    {
        $wood = Wood::create('oak');
        $newWood = Wood::create('pine');
        $material = PlywoodMaterial::create($wood);
        $existingMaterial = PlywoodMaterial::create($newWood);

        $materialId = $material->getId();
        $newWoodId = $newWood->getId();

        $command = new EditMaterialCommand(
            materialId: $materialId,
            woodId: $newWoodId,
            enabled: true
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with($newWoodId)
            ->willReturn($newWood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->with($newWood, MaterialType::PLYWOOD)
            ->willReturn($existingMaterial)
        ;

        $this->expectException(MaterialAlreadyExistsException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleDoesNotThrowExceptionWhenSameMaterialFound(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $materialId = $material->getId();
        $woodId = $wood->getId();

        // Trying to update material with same wood - should be allowed
        $command = new EditMaterialCommand(
            materialId: $materialId,
            woodId: $woodId,
            enabled: false
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

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
            ->willReturn($material)  // Returns same material - should be OK
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);

        $this->assertSame($wood, $material->getWood());
        $this->assertFalse($material->isEnabled());
    }

    public function testHandleUpdatesOnlyEnabledStatus(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood, true);

        $materialId = $material->getId();
        $woodId = $wood->getId();

        // Update only enabled status, keep same wood
        $command = new EditMaterialCommand(
            materialId: $materialId,
            woodId: $woodId,
            enabled: false
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with($woodId)
            ->willReturn($wood)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findByWoodAndType')
            ->willReturn($material)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);

        $this->assertSame($wood, $material->getWood());
        $this->assertFalse($material->isEnabled());
    }
}
