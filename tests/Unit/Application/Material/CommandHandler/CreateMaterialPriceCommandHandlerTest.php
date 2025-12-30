<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Command\Material\CreateMaterialPrice\CreateMaterialPriceCommand;
use App\Application\Command\Material\CreateMaterialPrice\CreateMaterialPriceCommandHandler;
use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Material\Exception\MaterialNotFoundException;
use App\Domain\Material\Exception\MaterialPriceAlreadyExistsException;
use App\Domain\Material\Exception\MaterialPriceValidationException;
use App\Domain\Wood\Entity\Wood;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateMaterialPriceCommandHandlerTest extends TestCase
{
    private MaterialApplicationService&MockObject $materialService;
    private CreateMaterialPriceCommandHandler $handler;

    protected function setUp(): void
    {
        $this->materialService = $this->createMock(MaterialApplicationService::class);
        $this->handler = new CreateMaterialPriceCommandHandler($this->materialService);
    }

    public function testHandleCreatesMaterialPriceSuccessfully(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $command = new CreateMaterialPriceCommand(
            materialId: 1,
            thickness: 18,
            price: '1500.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($material)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findMaterialPriceByMaterialAndThickness')
            ->with($material, 18)
            ->willReturn(null)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
            ->with($material)
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenMaterialNotFound(): void
    {
        $command = new CreateMaterialPriceCommand(
            materialId: 999,
            thickness: 18,
            price: '1500.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null)
        ;

        $this->expectException(MaterialNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsValidationExceptionForInvalidThickness(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $command = new CreateMaterialPriceCommand(
            materialId: 1,
            thickness: 0,
            price: '1500.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('findById')
            ->willReturn($material)
        ;

        $this->expectException(MaterialPriceValidationException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsValidationExceptionForInvalidPrice(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $command = new CreateMaterialPriceCommand(
            materialId: 1,
            thickness: 18,
            price: '0.50'
        );

        $this->materialService
            ->expects($this->once())
            ->method('findById')
            ->willReturn($material)
        ;

        $this->expectException(MaterialPriceValidationException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenPriceAlreadyExists(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $existingPrice = $this->createMock(MaterialPrice::class);

        $command = new CreateMaterialPriceCommand(
            materialId: 1,
            thickness: 18,
            price: '1500.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('findById')
            ->willReturn($material)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findMaterialPriceByMaterialAndThickness')
            ->with($material, 18)
            ->willReturn($existingPrice)
        ;

        $this->expectException(MaterialPriceAlreadyExistsException::class);

        $this->handler->__invoke($command);
    }
}
