<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Color\CommandHandler;

use App\Application\Command\Color\EditColor\EditColorCommand;
use App\Application\Command\Color\EditColor\EditColorCommandHandler;
use App\Application\Service\ColorApplicationService;
use App\Domain\Color\Entity\Color;
use App\Domain\Color\Exception\ColorNotFoundException;
use App\Domain\Color\Exception\ColorValidationException;
use App\Domain\Translation\TranslationDto\TranslationDto;
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
        $color = Color::create(5000, false, true);

        $command = new EditColorCommand(
            id: 1,
            code: 5000,
            inStock: true,
            enabled: false,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($color)
        ;

        $this->colorApplicationService
            ->expects($this->once())
            ->method('save')
            ->with($color)
        ;

        $this->handler->__invoke($command);

        $this->assertTrue($color->isInStock());
        $this->assertFalse($color->isEnabled());
    }

    public function testHandleUpdatesColorCode(): void
    {
        $color = Color::create(5000);

        $reflection = new \ReflectionClass($color);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($color, 1);

        $command = new EditColorCommand(
            id: 1,
            code: 6000,
            inStock: false,
            enabled: true,
            translations: []
        );

        // findById se volá dvakrát
        $this->colorApplicationService
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnCallback(function ($id) use ($color) {
                if ($id === 1) {
                    return $color;
                }
            })
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

        $this->assertSame(6000, $color->getCode());
    }

    public function testHandleThrowsExceptionWhenColorNotFound(): void
    {
        $command = new EditColorCommand(
            id: 999,
            code: 5000,
            inStock: false,
            enabled: true,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null)
        ;

        $this->expectException(ColorNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsValidationException(): void
    {
        $color = Color::create(5000);

        $command = new EditColorCommand(
            id: 1,
            code: 999,
            inStock: false,
            enabled: true,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('findById')
            ->willReturn($color)
        ;

        $this->expectException(ColorValidationException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleWithTranslations(): void
    {
        $color = Color::create(5000);

        $descriptionTranslation = $this->createMock(TranslationDto::class);
        $descriptionTranslation->method('getField')->willReturn('description');
        $descriptionTranslation->method('getValue')->willReturn('Updated description');
        $descriptionTranslation->method('getLocale')->willReturn('en');

        $command = new EditColorCommand(
            id: 1,
            code: 5000,
            inStock: true,
            enabled: true,
            translations: [$descriptionTranslation]
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('findById')
            ->willReturn($color)
        ;

        $this->colorApplicationService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);
    }
}
