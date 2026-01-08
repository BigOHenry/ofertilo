<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Material\Command\EditMaterialPrice\EditMaterialPriceCommand;
use App\Application\Material\Command\EditMaterialPrice\EditMaterialPriceCommandHandler;
use App\Application\Material\Service\MaterialApplicationService;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Material\Exception\MaterialPriceAlreadyExistsException;
use App\Domain\Material\Exception\MaterialPriceNotFoundException;
use App\Domain\Material\Exception\MaterialPriceValidationException;
use App\Domain\Wood\Entity\Wood;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EditMaterialPriceCommandHandlerTest extends TestCase
{
    private MaterialApplicationService&MockObject $materialService;
    private EditMaterialPriceCommandHandler $handler;

    protected function setUp(): void
    {
        $this->materialService = $this->createMock(MaterialApplicationService::class);
        $this->handler = new EditMaterialPriceCommandHandler($this->materialService);
    }

    public function testHandleUpdatesMaterialPriceSuccessfully(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $material->addPrice(18, '1500.00');

        $materialId = $material->getId();
        $materialPrice = $material->getPrices()->first();

        $this->assertInstanceOf(MaterialPrice::class, $materialPrice);
        $priceId = $materialPrice->getId();

        $command = new EditMaterialPriceCommand(
            materialId: $materialId,
            priceId: $priceId,
            thickness: 20,
            price: '1700.50'
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

        $this->assertSame(20, $materialPrice->getThickness());
        $this->assertSame('1700.50', $materialPrice->getPrice());
    }

    public function testHandleThrowsExceptionWhenMaterialPriceNotFound(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $materialId = $material->getId();
        $nonExistentPriceId = '00000000-0000-0000-0000-000000000999';

        $command = new EditMaterialPriceCommand(
            materialId: $materialId,
            priceId: $nonExistentPriceId,
            thickness: 20,
            price: '1700.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

        $this->expectException(MaterialPriceNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsValidationException(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $material->addPrice(18, '1500.00');

        $materialId = $material->getId();
        $materialPrice = $material->getPrices()->first();

        $this->assertInstanceOf(MaterialPrice::class, $materialPrice);
        $priceId = $materialPrice->getId();

        $command = new EditMaterialPriceCommand(
            materialId: $materialId,
            priceId: $priceId,
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

    public function testHandleThrowsExceptionWhenThicknessAlreadyExists(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $material->addPrice(18, '1500.00');
        $material->addPrice(20, '1700.00');

        $materialId = $material->getId();
        $materialPrice = $material->getPrices()->first();

        $this->assertInstanceOf(MaterialPrice::class, $materialPrice);
        $priceId = $materialPrice->getId();

        // Try to change thickness from 18 to 20 (which already exists)
        $command = new EditMaterialPriceCommand(
            materialId: $materialId,
            priceId: $priceId,
            thickness: 20,  // Already exists
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

    public function testHandleUpdatesOnlyPrice(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $material->addPrice(18, '1500.00');

        $materialId = $material->getId();
        $materialPrice = $material->getPrices()->first();

        $this->assertInstanceOf(MaterialPrice::class, $materialPrice);
        $priceId = $materialPrice->getId();

        // Update only price, keep thickness the same
        $command = new EditMaterialPriceCommand(
            materialId: $materialId,
            priceId: $priceId,
            thickness: 18,  // Same thickness
            price: '1800.00'  // New price
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

        $this->assertSame(18, $materialPrice->getThickness());
        $this->assertSame('1800.00', $materialPrice->getPrice());
    }

    public function testHandleValidatesPriceFormat(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $material->addPrice(18, '1500.00');

        $materialId = $material->getId();
        $materialPrice = $material->getPrices()->first();

        $this->assertInstanceOf(MaterialPrice::class, $materialPrice);
        $priceId = $materialPrice->getId();

        $command = new EditMaterialPriceCommand(
            materialId: $materialId,
            priceId: $priceId,
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
}
