<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Wood\CommandHandler;

use App\Application\Command\Wood\DeleteWood\DeleteWoodCommand;
use App\Application\Command\Wood\DeleteWood\DeleteWoodCommandHandler;
use App\Application\Service\WoodApplicationService;
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

        $command = DeleteWoodCommand::create(1);

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with(1)
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
        $command = DeleteWoodCommand::create(999);

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willThrowException(WoodNotFoundException::withId(999))
        ;

        $this->expectException(WoodNotFoundException::class);

        $this->handler->__invoke($command);
    }
}
