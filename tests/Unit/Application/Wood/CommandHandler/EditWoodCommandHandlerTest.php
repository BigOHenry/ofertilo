<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Wood\CommandHandler;

use App\Application\Command\Wood\EditWood\EditWoodCommand;
use App\Application\Command\Wood\EditWood\EditWoodCommandHandler;
use App\Application\Service\WoodApplicationService;
use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Exception\WoodNotFoundException;
use App\Domain\Wood\Exception\WoodValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EditWoodCommandHandlerTest extends TestCase
{
    private WoodApplicationService&MockObject $woodApplicationService;
    private EditWoodCommandHandler $handler;

    protected function setUp(): void
    {
        $this->woodApplicationService = $this->createMock(WoodApplicationService::class);
        $this->handler = new EditWoodCommandHandler($this->woodApplicationService);
    }

    public function testHandleUpdatesWoodSuccessfully(): void
    {
        $wood = Wood::create('oak', 'Quercus', 750, 6000);

        $command = new EditWoodCommand(
            id: 1,
            name: 'oak',
            latinName: 'Quercus robur',
            dryDensity: 800,
            hardness: 6500,
            enabled: true,
            translations: []
        );

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($wood)
        ;

        $this->woodApplicationService
            ->expects($this->once())
            ->method('save')
            ->with($wood)
        ;

        $this->handler->__invoke($command);

        $this->assertSame('Quercus robur', $wood->getLatinName());
        $this->assertSame(800, $wood->getDryDensity());
        $this->assertSame(6500, $wood->getHardness());
    }

    public function testHandleThrowsExceptionWhenWoodNotFound(): void
    {
        $command = new EditWoodCommand(
            id: 999,
            name: 'oak',
            latinName: null,
            dryDensity: null,
            hardness: null,
            enabled: true,
            translations: []
        );

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willThrowException(WoodNotFoundException::withId(999))
        ;

        $this->expectException(WoodNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsValidationException(): void
    {
        $wood = Wood::create('oak');

        $command = new EditWoodCommand(
            id: 1,
            name: 'oak',  // Same name - no duplication check
            latinName: null,
            dryDensity: -100,  // Invalid value
            hardness: null,
            enabled: true,
            translations: []
        );

        $this->woodApplicationService
            ->expects($this->once())
            ->method('getById')
            ->willReturn($wood)
        ;

        $this->expectException(WoodValidationException::class);

        $this->handler->__invoke($command);
    }
}
