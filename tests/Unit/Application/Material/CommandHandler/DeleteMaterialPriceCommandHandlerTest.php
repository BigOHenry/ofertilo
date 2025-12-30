<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Command\Material\DeleteMaterialPrice\DeleteMaterialPriceCommand;
use App\Application\Command\Material\DeleteMaterialPrice\DeleteMaterialPriceCommandHandler;
use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Entity\MaterialPrice;
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
        $materialPrice = MaterialPrice::create($material, 18, '1500.00');

        $command = DeleteMaterialPriceCommand::create(1);

        $this->materialService
            ->expects($this->once())
            ->method('findMaterialPriceById')
            ->with(1)
            ->willReturn($materialPrice)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('deleteMaterialPrice')
            ->with($materialPrice)
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenMaterialPriceNotFound(): void
    {
        $command = DeleteMaterialPriceCommand::create(999);

        $this->materialService
            ->expects($this->once())
            ->method('findMaterialPriceById')
            ->with(999)
            ->willReturn(null)
        ;

        $this->expectException(MaterialPriceNotFoundException::class);

        $this->handler->__invoke($command);
    }
}
