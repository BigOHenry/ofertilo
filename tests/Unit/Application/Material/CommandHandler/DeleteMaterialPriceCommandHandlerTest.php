<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Material\Command\DeleteMaterialPrice\DeleteMaterialPriceCommand;
use App\Application\Material\Command\DeleteMaterialPrice\DeleteMaterialPriceCommandHandler;
use App\Application\Material\Service\MaterialApplicationService;
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
        $material->addPrice(18, '1500.00');

        $materialId = $material->getId();
        $materialPrice = $material->getPrices()->first();

        $this->assertInstanceOf(MaterialPrice::class, $materialPrice);
        $priceId = $materialPrice->getId();

        $command = DeleteMaterialPriceCommand::create($materialId, $priceId);

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

        $this->assertCount(0, $material->getPrices());
    }

    public function testHandleThrowsExceptionWhenMaterialPriceNotFound(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $materialId = $material->getId();
        $nonExistentPriceId = '00000000-0000-0000-0000-000000000999';

        $command = DeleteMaterialPriceCommand::create($materialId, $nonExistentPriceId);

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

        $this->expectException(MaterialPriceNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleDeletesCorrectPrice(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $material->addPrice(18, '1500.00');
        $material->addPrice(20, '1700.00');
        $material->addPrice(25, '2000.00');

        $materialId = $material->getId();

        // Get the second price (20mm)
        $prices = $material->getPrices()->toArray();
        $this->assertCount(3, $prices);

        $priceToDelete = $prices[1]; // 20mm
        $this->assertInstanceOf(MaterialPrice::class, $priceToDelete);
        $this->assertSame(20, $priceToDelete->getThickness());

        $priceId = $priceToDelete->getId();

        $command = DeleteMaterialPriceCommand::create($materialId, $priceId);

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

        // Should have 2 prices left
        $this->assertCount(2, $material->getPrices());

        // Verify the 20mm price was deleted
        $this->assertNull($material->findPriceByThickness(20));

        // Verify other prices still exist
        $this->assertNotNull($material->findPriceByThickness(18));
        $this->assertNotNull($material->findPriceByThickness(25));
    }

    public function testHandleDeletesLastPrice(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $material->addPrice(18, '1500.00');

        $materialId = $material->getId();
        $materialPrice = $material->getPrices()->first();

        $this->assertInstanceOf(MaterialPrice::class, $materialPrice);
        $priceId = $materialPrice->getId();

        $this->assertCount(1, $material->getPrices());

        $command = DeleteMaterialPriceCommand::create($materialId, $priceId);

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

        $this->assertCount(0, $material->getPrices());
    }

    public function testHandleCallsSaveExactlyOnce(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $material->addPrice(18, '1500.00');

        $materialId = $material->getId();
        $materialPrice = $material->getPrices()->first();

        $this->assertInstanceOf(MaterialPrice::class, $materialPrice);
        $priceId = $materialPrice->getId();

        $command = DeleteMaterialPriceCommand::create($materialId, $priceId);

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->willReturn($material)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('save')
            ->with($this->identicalTo($material))
        ;

        $this->handler->__invoke($command);
    }
}
