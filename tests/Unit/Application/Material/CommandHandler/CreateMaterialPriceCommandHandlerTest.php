<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Material\Command\CreateMaterialPrice\CreateMaterialPriceCommand;
use App\Application\Material\Command\CreateMaterialPrice\CreateMaterialPriceCommandHandler;
use App\Application\Material\Service\MaterialApplicationService;
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

        $materialId = $material->getId();

        $command = new CreateMaterialPriceCommand(
            materialId: $materialId,
            thickness: 18,
            price: '1500.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);

        $this->assertCount(1, $material->getPrices());

        $createdPrice = $material->getPrices()->first();
        $this->assertInstanceOf(MaterialPrice::class, $createdPrice);
        $this->assertSame(18, $createdPrice->getThickness());
        $this->assertSame('1500.00', $createdPrice->getPrice());
    }

    public function testHandleThrowsExceptionWhenMaterialNotFound(): void
    {
        $nonExistentId = '00000000-0000-0000-0000-000000000999';

        $command = new CreateMaterialPriceCommand(
            materialId: $nonExistentId,
            thickness: 18,
            price: '1500.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($nonExistentId)
            ->willThrowException(MaterialNotFoundException::withId($nonExistentId))
        ;

        $this->expectException(MaterialNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsValidationExceptionForInvalidThickness(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $materialId = $material->getId();

        $command = new CreateMaterialPriceCommand(
            materialId: $materialId,
            thickness: -5,  // Invalid negative thickness
            price: '1500.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

        $this->expectException(MaterialPriceValidationException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsValidationExceptionForInvalidPrice(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $materialId = $material->getId();

        $command = new CreateMaterialPriceCommand(
            materialId: $materialId,
            thickness: 18,
            price: '-100.00'  // Invalid negative price
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
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

        $materialId = $material->getId();

        $command = new CreateMaterialPriceCommand(
            materialId: $materialId,
            thickness: 18,  // Same thickness already exists
            price: '1600.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

        $this->expectException(MaterialPriceAlreadyExistsException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleCreatesMultiplePricesForDifferentThicknesses(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $materialId = $material->getId();

        // Create first price
        $command1 = new CreateMaterialPriceCommand(
            materialId: $materialId,
            thickness: 18,
            price: '1500.00'
        );

        $this->materialService
            ->expects($this->exactly(2))
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

        $this->materialService
            ->expects($this->exactly(2))
            ->method('save')
        ;

        $this->handler->__invoke($command1);
        $this->assertCount(1, $material->getPrices());

        // Create second price
        $command2 = new CreateMaterialPriceCommand(
            materialId: $materialId,
            thickness: 20,
            price: '1700.00'
        );

        $this->handler->__invoke($command2);
        $this->assertCount(2, $material->getPrices());
    }

    public function testHandleCreatedPriceHasUuid(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $materialId = $material->getId();

        $command = new CreateMaterialPriceCommand(
            materialId: $materialId,
            thickness: 18,
            price: '1500.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);

        $createdPrice = $material->getPrices()->first();
        $this->assertInstanceOf(MaterialPrice::class, $createdPrice);

        $priceId = $createdPrice->getId();
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $priceId,
            'Price ID should be a valid UUID v4'
        );
    }
}
