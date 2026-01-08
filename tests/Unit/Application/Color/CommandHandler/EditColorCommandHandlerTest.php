<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Color\CommandHandler;

use App\Application\Color\Command\EditColor\EditColorCommand;
use App\Application\Color\Command\EditColor\EditColorCommandHandler;
use App\Application\Color\Service\ColorApplicationService;
use App\Domain\Color\Entity\Color;
use App\Domain\Color\Exception\ColorAlreadyExistsException;
use App\Domain\Color\Exception\ColorNotFoundException;
use App\Domain\Color\Exception\ColorValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EditColorCommandHandlerTest extends TestCase
{
    private ColorApplicationService&MockObject $colorApplicationService;
    private EditColorCommandHandler $handler;

    protected function setUp(): void
    {
        $this->colorApplicationService = $this->createMock(ColorApplicationService::class);
        $this->handler = new EditColorCommandHandler($this->colorApplicationService);
    }

    public function testHandleUpdatesColorSuccessfully(): void
    {
        $color = Color::create(5000);
        $colorId = $color->getId();

        $command = new EditColorCommand(
            id: $colorId,
            code: 5000, // Same code - no check for duplication
            inStock: true,
            enabled: false,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($colorId)
            ->willReturn($color)
        ;

        $this->colorApplicationService
            ->expects($this->never())
            ->method('findByCode')
        ;

        $this->colorApplicationService
            ->expects($this->once())
            ->method('save')
            ->with($color)
        ;

        $this->handler->__invoke($command);

        $this->assertSame(5000, $color->getCode());
        $this->assertTrue($color->isInStock());
        $this->assertFalse($color->isEnabled());
    }

    public function testHandleUpdatesColorCode(): void
    {
        $color = Color::create(5000);
        $colorId = $color->getId();

        $command = new EditColorCommand(
            id: $colorId,
            code: 6000,
            inStock: false,
            enabled: true,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($colorId)
            ->willReturn($color)
        ;

        $this->colorApplicationService
            ->expects($this->once())
            ->method('findByCode')
            ->with(6000)
            ->willReturn(null)
        ;

        $this->colorApplicationService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);

        $this->assertSame(6000, $color->getCode());
        $this->assertFalse($color->isInStock());
        $this->assertTrue($color->isEnabled());
    }

    public function testHandleThrowsExceptionWhenColorNotFound(): void
    {
        $nonExistentId = '00000000-0000-0000-0000-000000000999';

        $command = new EditColorCommand(
            id: $nonExistentId,
            code: 5000,
            inStock: false,
            enabled: true,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($nonExistentId)
            ->willThrowException(ColorNotFoundException::withId($nonExistentId))
        ;

        $this->expectException(ColorNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenCodeAlreadyExists(): void
    {
        $color = Color::create(5000);
        $existingColor = Color::create(6000);

        $colorId = $color->getId();

        $command = new EditColorCommand(
            id: $colorId,
            code: 6000,
            inStock: false,
            enabled: true,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($colorId)
            ->willReturn($color)
        ;

        $this->colorApplicationService
            ->expects($this->once())
            ->method('findByCode')
            ->with(6000)
            ->willReturn($existingColor)
        ;

        $this->expectException(ColorAlreadyExistsException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsValidationException(): void
    {
        $color = Color::create(5000);
        $colorId = $color->getId();

        $command = new EditColorCommand(
            id: $colorId,
            code: 999,  // Invalid RAL code (should be 4 digits >= 1000)
            inStock: false,
            enabled: true,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($colorId)
            ->willReturn($color)
        ;

        $this->expectException(ColorValidationException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleUpdatesOnlyInStockStatus(): void
    {
        $color = Color::create(5000);
        $colorId = $color->getId();

        // Update only inStock, keep same code
        $command = new EditColorCommand(
            id: $colorId,
            code: 5000,
            inStock: false,
            enabled: true,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($colorId)
            ->willReturn($color)
        ;

        $this->colorApplicationService
            ->expects($this->never())
            ->method('findByCode')
        ;

        $this->colorApplicationService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);

        $this->assertSame(5000, $color->getCode());
        $this->assertFalse($color->isInStock());
        $this->assertTrue($color->isEnabled());
    }

    public function testHandleDoesNotThrowExceptionWhenUpdatingSameCode(): void
    {
        $color = Color::create(5000);
        $colorId = $color->getId();

        // Trying to update to same code - should be allowed
        $command = new EditColorCommand(
            id: $colorId,
            code: 5000,
            inStock: true,
            enabled: true,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('getById')
            ->with($colorId)
            ->willReturn($color)
        ;

        // Should not check for duplicates when code doesn't change
        $this->colorApplicationService
            ->expects($this->never())
            ->method('findByCode')
        ;

        $this->colorApplicationService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);

        $this->assertSame(5000, $color->getCode());
    }
}
