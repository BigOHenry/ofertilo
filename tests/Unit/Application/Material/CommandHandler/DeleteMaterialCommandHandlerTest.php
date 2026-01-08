<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Material\CommandHandler;

use App\Application\Material\Command\DeleteMaterial\DeleteMaterialCommand;
use App\Application\Material\Command\DeleteMaterial\DeleteMaterialCommandHandler;
use App\Application\Material\Service\MaterialApplicationService;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Material\Exception\MaterialNotFoundException;
use App\Domain\Wood\Entity\Wood;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteMaterialCommandHandlerTest extends TestCase
{
    private MaterialApplicationService&MockObject $materialService;
    private DeleteMaterialCommandHandler $handler;

    protected function setUp(): void
    {
        $this->materialService = $this->createMock(MaterialApplicationService::class);
        $this->handler = new DeleteMaterialCommandHandler($this->materialService);
    }

    public function testHandleDeletesMaterialSuccessfully(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $materialId = $material->getId();

        $command = DeleteMaterialCommand::create($materialId);

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('delete')
            ->with($material)
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenMaterialNotFound(): void
    {
        $nonExistentId = '00000000-0000-0000-0000-000000000999';

        $command = DeleteMaterialCommand::create($nonExistentId);

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($nonExistentId)
            ->willThrowException(MaterialNotFoundException::withId($nonExistentId))
        ;

        $this->expectException(MaterialNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleCallsDeleteOnlyOnce(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);

        $materialId = $material->getId();

        $command = DeleteMaterialCommand::create($materialId);

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->willReturn($material)
        ;

        // Verify delete is called exactly once with the same instance
        $this->materialService
            ->expects($this->once())
            ->method('delete')
            ->with($this->identicalTo($material))
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleDeletesMaterialWithPrices(): void
    {
        $wood = Wood::create('oak');
        $material = PlywoodMaterial::create($wood);
        $material->addPrice(18, '1500.00');
        $material->addPrice(20, '1700.00');

        $materialId = $material->getId();

        $this->assertCount(2, $material->getPrices());

        $command = DeleteMaterialCommand::create($materialId);

        $this->materialService
            ->expects($this->once())
            ->method('getById')
            ->with($materialId)
            ->willReturn($material)
        ;

        $this->materialService
            ->expects($this->once())
            ->method('delete')
            ->with($material)
        ;

        $this->handler->__invoke($command);
    }
}
