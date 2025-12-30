<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Command\Material\EditMaterialPrice\EditMaterialPriceCommand;
use App\Application\Command\Material\EditMaterialPrice\EditMaterialPriceCommandHandler;
use App\Application\Service\MaterialApplicationService;
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
        $materialPrice = MaterialPrice::create($material, 18, '1500.00');

        $command = new EditMaterialPriceCommand(
            id: 1,
            thickness: 20,
            price: '1700.50'
        );

        $this->materialService
            ->expects($this->once())
            ->method('findMaterialPriceById')
            ->with(1)
            ->willReturn($materialPrice)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findMaterialPriceByMaterialAndThickness')
            ->with($material, 20)
            ->willReturn(null)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('saveMaterialPrice')
            ->with($materialPrice)
        ;

        $this->handler->__invoke($command);

        $this->assertSame(20, $materialPrice->getThickness());
        $this->assertSame('1700.50', $materialPrice->getPrice());
    }

    public function testHandleThrowsExceptionWhenMaterialPriceNotFound(): void
    {
        $command = new EditMaterialPriceCommand(
            id: 999,
            thickness: 18,
            price: '1500.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('findMaterialPriceById')
            ->with(999)
            ->willReturn(null)
        ;

        $this->expectException(MaterialPriceNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsValidationException(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $materialPrice = MaterialPrice::create($material, 18, '1500.00');

        $command = new EditMaterialPriceCommand(
            id: 1,
            thickness: 0,
            price: '1500.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('findMaterialPriceById')
            ->willReturn($materialPrice)
        ;

        $this->expectException(MaterialPriceValidationException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenThicknessAlreadyExists(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $materialPrice = MaterialPrice::create($material, 18, '1500.00');
        $existingPrice = MaterialPrice::create($material, 20, '1700.00');

        $reflection = new \ReflectionClass($materialPrice);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($materialPrice, 1);

        $reflection2 = new \ReflectionClass($existingPrice);
        $idProperty2 = $reflection2->getProperty('id');
        $idProperty2->setAccessible(true);
        $idProperty2->setValue($existingPrice, 2);

        $command = new EditMaterialPriceCommand(
            id: 1,
            thickness: 20,
            price: '1500.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('findMaterialPriceById')
            ->willReturn($materialPrice)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('findMaterialPriceByMaterialAndThickness')
            ->willReturn($existingPrice)
        ;

        $this->expectException(MaterialPriceAlreadyExistsException::class);

        $this->handler->__invoke($command);
    }
}
