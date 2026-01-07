<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Wood\CommandHandler;

use App\Application\Wood\Command\DeleteWood\DeleteWoodCommand;
use App\Application\Wood\Command\DeleteWood\DeleteWoodCommandHandler;
use App\Application\Wood\Service\WoodApplicationService;
use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Exception\WoodNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteWoodCommandHandlerTest extends TestCase
{
    private WoodApplicationService&MockObject $woodApplicationService;
    private DeleteWoodCommandHandler $handler;

    protected function setUp(): void
    {
        $this->woodApplicationService = $this->createMock(WoodApplicationService::class);
        $this->handler = new DeleteWoodCommandHandler($this->woodApplicationService);
    }

    public function testHandleDeletesWoodSuccessfully(): void
    {
        $wood = Wood::create('oak');
        $woodId = $wood->getId();

        $command = DeleteWoodCommand::create($woodId);

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($woodId)
            ->willReturn($wood)
        ;

        $this->woodApplicationService
            ->expects($this->once())
            ->method('delete')
            ->with($wood)
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenWoodNotFound(): void
    {
        $nonExistentId = '00000000-0000-0000-0000-000000000999';

        $command = DeleteWoodCommand::create($nonExistentId);

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($nonExistentId)
            ->willThrowException(WoodNotFoundException::withId($nonExistentId))
        ;

        $this->expectException(WoodNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleCallsDeleteOnlyOnce(): void
    {
        $wood = Wood::create('oak');
        $woodId = $wood->getId();

        $command = DeleteWoodCommand::create($woodId);

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->willReturn($wood)
        ;

        // Verify delete is called exactly once
        $this->woodApplicationService
            ->expects($this->once())
            ->method('delete')
            ->with($this->identicalTo($wood))
        ;

        $this->handler->__invoke($command);
    }
}
