<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Command\Material\EditMaterial\EditMaterialCommand;
use App\Application\Command\Material\EditMaterial\EditMaterialCommandHandler;
use App\Application\Service\MaterialApplicationService;
use App\Application\Service\WoodApplicationService;
use App\Domain\Material\Entity\Material;
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

    public function testHandleUpdatesMataterialSuccessfully(): void
    {
        $wood = Wood::create('oak');
        $newWood = Wood::create('pine');
        $material = PlywoodMaterial::create($wood);

        $command = new EditMaterialCommand(
            id: 1,
            woodId: 2,
            enabled: false
        );

        // getById místo findById
        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($material)
        ;

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with(2)
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
        $command = new EditMaterialCommand(
            id: 999,
            woodId: 1,
            enabled: true
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willThrowException(MaterialNotFoundException::withId(999))
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

        $command = new EditMaterialCommand(
            id: 1,
            woodId: 999,
            enabled: true
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($material)
        ;

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willThrowException(WoodNotFoundException::withId(999))
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

        $reflection = new \ReflectionClass(Material::class);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($material, 1);
        $idProperty->setValue($existingMaterial, 2);

        $command = new EditMaterialCommand(
            id: 1,
            woodId: 2,
            enabled: true
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($material)
        ;

        $this->woodService
            ->expects($this->once())
            ->method('getById')
            ->with(2)
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
}
