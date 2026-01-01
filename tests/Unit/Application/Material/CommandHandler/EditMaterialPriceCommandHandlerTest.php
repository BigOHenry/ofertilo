<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Material\Command\EditMaterialPrice\EditMaterialPriceCommand;
use App\Application\Material\Command\EditMaterialPrice\EditMaterialPriceCommandHandler;
use App\Application\Material\Service\MaterialApplicationService;
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

        $materialPrice = $material->getPrices()[0];

        $reflection = new \ReflectionClass($materialPrice);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($materialPrice, 1);

        $command = new EditMaterialPriceCommand(
            materialId: 1,
            priceId: 1,
            thickness: 20,
            price: '1700.50'
        );

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

        $this->assertSame(20, $materialPrice->getThickness());
        $this->assertSame('1700.50', $materialPrice->getPrice());
    }

    public function testHandleThrowsExceptionWhenMaterialPriceNotFound(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $command = new EditMaterialPriceCommand(
            materialId: 1,
            priceId: 999,
            thickness: 20,
            price: '1700.00'
        );

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with(1)
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

        $materialPrice = $material->getPrices()[0];

        $reflection = new \ReflectionClass($materialPrice);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($materialPrice, 1);

        $command = new EditMaterialPriceCommand(
            materialId: 1,
            priceId: 1,
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

    public function testHandleThrowsExceptionWhenThicknessAlreadyExists(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $material->addPrice(18, '1500.00');
        $material->addPrice(20, '1700.00');

        $materialPrice = $material->getPrices()[0];

        $reflection = new \ReflectionClass($materialPrice);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($materialPrice, 1);

        $command = new EditMaterialPriceCommand(
            materialId: 1,
            priceId: 1,
            thickness: 20,
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
