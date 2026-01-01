<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Material\Command\CreateMaterialPrice\CreateMaterialPriceCommand;
use App\Application\Material\Command\CreateMaterialPrice\CreateMaterialPriceCommandHandler;
use App\Application\Material\Service\MaterialApplicationService;
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

        // getById místo findById
        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($material)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);

        $this->assertCount(1, $material->getPrices());
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
            ->method('getById')
            ->with(999)
            ->willThrowException(MaterialNotFoundException::withId(999))
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
            thickness: -5,
            price: '1500.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with(1)
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
            price: '-100.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($material)
        ;

        $this->expectException(MaterialPriceValidationException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenPriceAlreadyExists(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $material->addPrice(18, '1500.00');

        $command = new CreateMaterialPriceCommand(
            materialId: 1,
            thickness: 18,
            price: '1600.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($material)
        ;

        $this->expectException(MaterialPriceAlreadyExistsException::class);

        $this->handler->__invoke($command);
    }
}
