<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Material\Command\DeleteMaterialPrice\DeleteMaterialPriceCommand;
use App\Application\Material\Command\DeleteMaterialPrice\DeleteMaterialPriceCommandHandler;
use App\Application\Material\Service\MaterialApplicationService;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Material\Exception\MaterialPriceNotFoundException;
use App\Domain\Wood\Entity\Wood;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteMaterialPriceCommandHandlerTest extends TestCase
{
    private MaterialApplicationService&MockObject $materialService;
    private DeleteMaterialPriceCommandHandler $handler;

    protected function setUp(): void
    {
        $this->materialService = $this->createMock(MaterialApplicationService::class);
        $this->handler = new DeleteMaterialPriceCommandHandler($this->materialService);
    }

    public function testHandleDeletesMaterialPriceSuccessfully(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $material->addPrice(18, '1500.00');

        $materialPrice = $material->getPrices()[0];

        $reflection = new \ReflectionClass($materialPrice);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($materialPrice, 1);

        $command = DeleteMaterialPriceCommand::create(1, 1);

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

        $this->assertCount(0, $material->getPrices());
    }

    public function testHandleThrowsExceptionWhenMaterialPriceNotFound(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $command = DeleteMaterialPriceCommand::create(1, 999);

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($material)
        ;

        $this->expectException(MaterialPriceNotFoundException::class);

        $this->handler->__invoke($command);
    }
}
