<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Color\CommandHandler;

use App\Application\Command\Color\DeleteColor\DeleteColorCommand;
use App\Application\Command\Color\DeleteColor\DeleteColorCommandHandler;
use App\Application\Service\ColorApplicationService;
use App\Domain\Color\Entity\Color;
use App\Domain\Color\Exception\ColorNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteColorCommandHandlerTest extends TestCase
{
    private ColorApplicationService&MockObject $colorApplicationService;
    private DeleteColorCommandHandler $handler;

    protected function setUp(): void
    {
        $this->colorApplicationService = $this->createMock(ColorApplicationService::class);
        $this->handler = new DeleteColorCommandHandler($this->colorApplicationService);
    }

    public function testHandleDeletesColorSuccessfully(): void
    {
        $color = Color::create(5000);

        $command = DeleteColorCommand::create(1);

        $this->colorApplicationService
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($color)
        ;

        $this->colorApplicationService
            ->expects($this->once())
            ->method('delete')
            ->with($color)
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenColorNotFound(): void
    {
        $command = DeleteColorCommand::create(999);

        $this->colorApplicationService
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null)
        ;

        $this->expectException(ColorNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleDeletesColorWithDifferentIds(): void
    {
        $color1 = Color::create(5000);
        $color2 = Color::create(6000);

        $command1 = DeleteColorCommand::create(1);
        $command2 = DeleteColorCommand::create(2);

        $this->colorApplicationService
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnCallback(function ($id) use ($color1, $color2) {
                return $id === 1 ? $color1 : $color2;
            })
        ;

        $this->colorApplicationService
            ->expects($this->exactly(2))
            ->method('delete')
        ;

        $this->handler->__invoke($command1);
        $this->handler->__invoke($command2);
    }
}
