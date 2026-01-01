<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Color\CommandHandler;

use App\Application\Color\Command\DeleteColor\DeleteColorCommand;
use App\Application\Color\Command\DeleteColor\DeleteColorCommandHandler;
use App\Application\Color\Service\ColorApplicationService;
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
            ->method('getById')
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
            ->method('getById')
            ->with(999)
            ->willThrowException(ColorNotFoundException::withId(999))
        ;

        $this->expectException(ColorNotFoundException::class);

        $this->handler->__invoke($command);
    }
}
